<?php
/**
 * Multilingual Forum Titles and Descriptions - Event Listener
 * Handles language-specific forum title and description overrides
 * Includes both ACP save logic and frontend dynamic replacement
 *
 * @author Oprea Cristian
 * @copyright (c) 2025 ITandWebSolutions
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @link https://itandwebsolutions.ro
 */

namespace iws\multilingualtitles\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\user;
use phpbb\language\language;
use phpbb\db\driver\driver_interface;
use phpbb\template\template;
use phpbb\request\request_interface;
use phpbb\controller\helper;

class listener implements EventSubscriberInterface
{
    protected $db;
    protected $user;
    protected $request;
    protected $template;
    protected $lang;
    protected $helper;
    protected $table_prefix;

    public function __construct(driver_interface $db, user $user, request_interface $request, language $lang, template $template, helper $helper)
    {
        $this->db = $db;
        $this->user = $user;
        $this->request = $request;
        $this->lang = $lang;
        $this->template = $template;
        $this->helper = $helper;
        $this->table_prefix = $this->db->get_table_prefix();
    }

    public static function getSubscribedEvents()
    {
        return [
            'core.acp_manage_forums_initialise_data' => 'load_forum_translations',
            'core.acp_manage_forums_display_form'    => 'assign_translation_data',
            'core.acp_manage_forums_validate_data'   => 'validate_translation_fields',
            'core.acp_manage_forums_update_data_after' => 'save_forum_translations',
            'core.index_modify_forum_rows' => 'override_index_forum_names',
            'core.viewforum_get_forum_id'  => 'override_viewforum_title',
        ];
    }

    public function load_forum_translations($event)
    {
        $forum_id = (int) $event['forum_id'];
        if (!$forum_id) return;

        $sql = 'SELECT * FROM ' . $this->table_prefix . 'forum_translations WHERE forum_id = ' . $forum_id;
        $result = $this->db->sql_query($sql);

        $translations = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $translations[$row['lang_iso']] = [
                'forum_name' => $row['forum_name'],
                'forum_desc' => $row['forum_desc'],
            ];
        }
        $this->db->sql_freeresult($result);

        $event['forum_data']['multilingual_titles'] = $translations;
    }

    public function assign_translation_data($event)
    {
        $forum_data = $event['forum_data'];
        $translations = $forum_data['multilingual_titles'] ?? [];

        $sql = 'SELECT lang_iso, lang_english_name, lang_local_name FROM ' . $this->table_prefix . 'lang';
        $result = $this->db->sql_query($sql);

        $langs = [];
        $default_iso = $this->lang->get_iso();

        while ($row = $this->db->sql_fetchrow($result)) {
            $iso = $row['lang_iso'];
            if ($iso === $default_iso) {
                continue;
            }
            if (!isset($translations[$iso])) {
                $translations[$iso] = [
                    'forum_name' => '',
                    'forum_desc' => '',
                ];
            }
            $langs[] = [
                'ISO' => $iso,
                'NAME' => $row['lang_english_name'],
                'LOCAL_NAME' => $row['lang_local_name'],
                'TRANSLATION' => $translations[$iso],
            ];
        }
        $this->db->sql_freeresult($result);

        $this->template->assign_var('TRANSLATION_LANGS', $langs);
    }

    public function validate_translation_fields($event)
    {
        // Optional validation hook
    }

    public function save_forum_translations($event)
    {
        $forum_id = (int) $event['forum_data']['forum_id'];

        $sql = 'DELETE FROM ' . $this->table_prefix . 'forum_translations WHERE forum_id = ' . $forum_id;
        $this->db->sql_query($sql);

        $translations = $this->request->variable('forum_translation', [], true);
        foreach ($translations as $lang_iso => $data) {
            $sql_ary = [
                'forum_id' => $forum_id,
                'lang_iso' => $lang_iso,
                'forum_name' => $data['forum_name'],
                'forum_desc' => $data['forum_desc'],
            ];
            $this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'forum_translations ' . $this->db->sql_build_array('INSERT', $sql_ary));
        }
    }

    public function override_index_forum_names($event)
    {
        $forum_rows = $event['forum_rows'];
        $lang_iso = $this->lang->get_iso();

        foreach ($forum_rows as &$row) {
            $sql = 'SELECT forum_name, forum_desc FROM ' . $this->table_prefix . 'forum_translations
                    WHERE forum_id = ' . (int)$row['forum_id'] . " AND lang_iso = '" . $this->db->sql_escape($lang_iso) . "'";
            $result = $this->db->sql_query($sql);
            if ($translation = $this->db->sql_fetchrow($result)) {
                $row['forum_name'] = $translation['forum_name'] ?: $row['forum_name'];
                $row['forum_desc'] = $translation['forum_desc'] ?: $row['forum_desc'];
            }
            $this->db->sql_freeresult($result);
        }

        $event['forum_rows'] = $forum_rows;
    }

    public function override_viewforum_title($event)
    {
        $forum_data = $event['forum_data'];
        $lang_iso = $this->lang->get_iso();

        $sql = 'SELECT forum_name, forum_desc FROM ' . $this->table_prefix . 'forum_translations
                WHERE forum_id = ' . (int)$forum_data['forum_id'] . " AND lang_iso = '" . $this->db->sql_escape($lang_iso) . "'";
        $result = $this->db->sql_query($sql);
        if ($translation = $this->db->sql_fetchrow($result)) {
            $forum_data['forum_name'] = $translation['forum_name'] ?: $forum_data['forum_name'];
            $forum_data['forum_desc'] = $translation['forum_desc'] ?: $forum_data['forum_desc'];
        }
        $this->db->sql_freeresult($result);

        $event['forum_data'] = $forum_data;
    }
}
