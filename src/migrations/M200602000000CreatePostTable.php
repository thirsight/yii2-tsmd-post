<?php

namespace tsmd\post\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post}}`.
 */
class M200602000000CreatePostTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE TABLE {{%post}} (
    `poid`          int unsigned auto_increment primary key,
    `parentid`      int unsigned default 0  not null,
    `uid`           int unsigned default 0  not null,
    `type`          varchar(16)                not null,
    `status`        varchar(16)                not null,
    `slug`          varchar(128)               null,
    `title`         varchar(255)    default '' not null,
    `excerpt`       text                       null,
    `content`       mediumtext                 null,
    `password`      varchar(64)     default '' not null,
    `redirect`      varchar(255)    default '' not null,
    `fileBase`      varchar(255)    default '' not null,
    `filePath`      varchar(255)    default '' not null,
    `mimeType`      varchar(64)     default '' not null,
    `fileSize`      bigint          default 0  not null,
    `imageWidth`    int             default 0  not null,
    `imageHeight`   int             default 0  not null,
    `commentOpen`   tinyint         default 0  not null,
    `commentCount`  int             default 0  not null,
    `objTable`      varchar(64)     default '' not null,
    `objid`         varchar(64)     default '' not null,
    `publishedTime` int             default 0  not null,
    `createdTime`   int                        not null,
    `updatedTime`   int                        not null,
    unique key `slug` (`slug`),
    index parentid (parentid),
    index uid (uid),
    index typeStatus (`type`, `status`),
    index objidTable (objid, objTable),
    index publishedTime (`publishedTime`),
    index createdTime (`createdTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE {{%post}} AUTO_INCREMENT = 10001;
SQL;
        $this->getDb()->createCommand($sql)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%post}}');
    }
}
