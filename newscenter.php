<?php
/**
 * Module Name: News Center
 * Module ID:   newscenterposts
 * Description: Lists recent NewsCenter articles with status, author, and quick action links.
 * Version:     1.0
 * Default W:   8
 * Default H:   4
 */

if (!defined('IN_GS')) { die('You cannot load this page directly.'); }

// Load module localization helper
$i18n_m = function_exists('dash_module_i18n') ? dash_module_i18n('newscenter') : function($key) { return $key; };

$uid = 'nc_' . substr(md5(__FILE__), 0, 6);

$articles   = array();
$plugin_ok  = function_exists('nc_get_db');
$db_ok      = false;

if ($plugin_ok) {
    try {
        $db = nc_get_db();
        if ($db) {
            $stmt = $db->query("
                SELECT id, title, slug, author, status, published_at, gallery 
                FROM nc_articles 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $db_ok = true;
        }
    } catch (\Throwable $e) {
        $db_ok = false;
    }
}

// Fallback date format check
$date_format = 'Y-m-d';
if (function_exists('nc_get_settings')) {
    $settings = nc_get_settings();
    if (!empty($settings['date_format'])) {
        $date_format = $settings['date_format'];
    }
}
?>

<style>
#<?php echo $uid; ?> .nc-mod-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
#<?php echo $uid; ?> .nc-mod-table th {
    text-align: left;
    padding: 6px 8px;
    border-bottom: 2px solid #eee;
    color: #888;
    font-weight: 600;
    white-space: nowrap;
}
#<?php echo $uid; ?> .nc-mod-table td {
    padding: 6px 8px;
    border-bottom: 1px solid #f3f3f3;
    vertical-align: middle;
}
#<?php echo $uid; ?> .nc-mod-table tr:last-child td { border-bottom: none; }
#<?php echo $uid; ?> .nc-mod-table tr:hover td { background: #fafafa; }
#<?php echo $uid; ?> .nc-mod-title {
    font-weight: 500;
    color: #333;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#<?php echo $uid; ?> .nc-mod-author {
    color: #666;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 11px;
}
#<?php echo $uid; ?> .nc-mod-date { color: #888; white-space: nowrap; font-size: 11px; }
#<?php echo $uid; ?> .nc-mod-actions { white-space: nowrap; }
#<?php echo $uid; ?> .nc-mod-btn {
    display: inline-block;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 11px;
    text-decoration: none;
    margin-left: 4px;
    border: 1px solid transparent;
    color: #fff;
}
#<?php echo $uid; ?> .nc-mod-btn-edit { background: #2563eb; }
#<?php echo $uid; ?> .nc-mod-btn-edit:hover { background: #1d4ed8; }
#<?php echo $uid; ?> .nc-mod-btn-new { background: #2563eb; font-size: 12px; padding: 4px 10px; }
#<?php echo $uid; ?> .nc-mod-btn-new:hover { background: #1d4ed8; }
#<?php echo $uid; ?> .nc-mod-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
}
#<?php echo $uid; ?> .nc-mod-badge-pub  { background: #dcfce7; color: #166534; }
#<?php echo $uid; ?> .nc-mod-badge-sch  { background: #e0e7ff; color: #3730a3; }
#<?php echo $uid; ?> .nc-mod-badge-drf  { background: #fef9c3; color: #854d0e; }
#<?php echo $uid; ?> .nc-mod-empty { color: #aaa; font-style: italic; text-align: center; padding: 16px; }
#<?php echo $uid; ?> .nc-mod-missing {
    color: #856404;
    background: #fff3cd;
    border: 1px solid #ffeeba;
    border-radius: 6px;
    padding: 10px 12px;
    font-size: 13px;
}
#<?php echo $uid; ?> .nc-mod-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
#<?php echo $uid; ?> .nc-mod-header h3 { margin: 0; font-size: 15px; }
</style>

<div id="<?php echo $uid; ?>">
    <div class="nc-mod-header">
        <h3>
            <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle; margin-right:4px;" width="22" height="22" viewBox="0 0 24 24">
                <path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 14H4V6h16zM6 10h12v2H6zm0 4h8v2H6zm0-6h12v1H6z"/>
            </svg>
            <?php echo $i18n_m('nc_mod_News_Center'); ?>
        </h3>
        <a class="nc-mod-btn nc-mod-btn-new" href="load.php?id=NewsCenter&action=edit-article">+ <?php echo $i18n_m('nc_mod_New_Article'); ?></a>
    </div>

    <?php if (!$plugin_ok): ?>
        <p class="nc-mod-missing">⚠ <?php echo $i18n_m('nc_mod_plugin_not_active'); ?>.</p>
    <?php elseif (!$db_ok): ?>
        <p class="nc-mod-missing">⚠ <?php echo $i18n_m('nc_mod_no_db_found'); ?>.</p>
    <?php elseif (empty($articles)): ?>
        <p class="nc-mod-empty"><?php echo $i18n_m('nc_mod_No_articles_found'); ?>.</p>
    <?php else: ?>
    <table class="nc-mod-table">
        <thead>
            <tr>
                <th><?php echo $i18n_m('nc_mod_Title'); ?></th>
                <th><?php echo $i18n_m('nc_mod_Author'); ?></th>
                <th><?php echo $i18n_m('nc_mod_Date'); ?></th>
                <th><?php echo $i18n_m('nc_mod_Status'); ?></th>
                <th style="text-align:center;"><?php echo $i18n_m('nc_mod_Action'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $art):
                $title   = htmlspecialchars($art['title']);
                $date    = date($date_format, strtotime($art['published_at']));
                $editUrl = 'load.php?id=NewsCenter&action=edit-article&article_id=' . (int)$art['id'];

                $author_name = $art['author'];
                if (function_exists('nc_get_article_authors_data')) {
                    $author_data = nc_get_article_authors_data($art['id'], $art['author']);
                    if (!empty($author_data['author_name'])) {
                        $author_name = $author_data['author_name'];
                    }
                }
                $author_name = htmlspecialchars($author_name);

                $is_future = strtotime($art['published_at']) > time();
                if ($art['status'] === 'published' && $is_future) {
                    $status_label = $i18n_m('nc_mod_Scheduled');
                    $status_class = 'nc-mod-badge-sch';
                } elseif ($art['status'] === 'published') {
                    $status_label = $i18n_m('nc_mod_Published');
                    $status_class = 'nc-mod-badge-pub';
                } else {
                    $status_label = $i18n_m('nc_mod_Draft');
                    $status_class = 'nc-mod-badge-drf';
                }
            ?>
            <tr>
                <td class="nc-mod-title" title="<?php echo $title; ?>">
                    <a href="<?php echo $editUrl; ?>" style="color:inherit; text-decoration:none; font-weight:600;">
                        <?php echo $title; ?>
                    </a>
                </td>
                <td class="nc-mod-author" title="<?php echo $author_name; ?>"><?php echo $author_name; ?></td>
                <td class="nc-mod-date"><?php echo $date; ?></td>
                <td>
                    <span class="nc-mod-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                </td>
                <td class="nc-mod-actions" style="text-align:center;">
                    <a class="nc-mod-btn nc-mod-btn-edit" href="<?php echo $editUrl; ?>" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;" width="13" height="13" viewBox="0 0 16 16">
                            <rect width="16" height="16" fill="none"/>
                            <path fill="#fff" d="M10.529 1.764a2.621 2.621 0 1 1 3.707 3.707l-.779.779L9.75 2.543zM9.043 3.25L2.657 9.636a2.96 2.96 0 0 0-.772 1.354l-.87 3.386a.5.5 0 0 0 .61.608l3.385-.869a2.95 2.95 0 0 0 1.354-.772l6.386-6.386z"/>
                        </svg>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>