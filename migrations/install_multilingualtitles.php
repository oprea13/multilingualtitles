<?php
/**
 * Multilingual Forum Titles and Descriptions - Migration File
 * Creates table for multilingual forum name and description storage
 *
 * @author Oprea Cristian
 * @copyright (c) 2025 ITandWebSolutions
 * @license GNU General Public License, version 2 (GPL-2.0)
 * @link https://itandwebsolutions.ro
 */

namespace iws\multilingualtitles\migrations;

class install_multilingualtitles extends \phpbb\db\migration\migration
{
    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'forum_translations' => [
                    'COLUMNS' => [
                        'forum_id'   => ['UINT', 0],
                        'lang_iso'   => ['VCHAR:10', ''],
                        'forum_name' => ['VCHAR_UNI:255', ''],
                        'forum_desc' => ['TEXT_UNI', ''],
                    ],
                    'PRIMARY_KEY' => ['forum_id', 'lang_iso'],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'forum_translations',
            ],
        ];
    }
}
