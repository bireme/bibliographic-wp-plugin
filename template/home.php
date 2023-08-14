<?php
/*
Template Name: Bibliographic Home
*/
global $biblio_service_url, $biblio_plugin_slug, $biblio_texts;

require_once(BIBLIOGRAPHIC_PLUGIN_PATH . '/lib/Paginator.php');

$order = array(
        'RELEVANCE' => 'score desc',
        'YEAR_ASC'  => 'publication_year asc',
        'YEAR_DESC' => 'publication_year desc'
    );

$biblio_config         = get_option('biblio_config');
$biblio_initial_filter = $biblio_config['initial_filter'];
$biblio_addthis_id     = $biblio_config['addthis_profile_id'];
$alternative_links     = (bool)$biblio_config['alternative_links'];

$site_language = strtolower(get_bloginfo('language'));
$lang = substr($site_language,0,2);

// set query using default param q (query) or s (wordpress search) or newexpr (metaiah)
$query = sanitize_text_field($_GET['s']) . sanitize_text_field($_GET['q']);
$query = stripslashes( trim($query) );

$sanitize_user_filter = sanitize_text_field($_GET['filter']);
$user_filter = stripslashes($sanitize_user_filter);
$page   = ( !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : 1 );
$format = ( !empty($_GET['format']) ? sanitize_text_field($_GET['format']) : '' );
$sanitize_sort = sanitize_text_field($_GET['sort']);
$sort   = ( !empty($_GET['sort']) ? $order[$sanitize_sort] : '');
$count  = ( !empty($_GET['count']) ? sanitize_text_field($_GET['count']) : 10 );
$total  = 0;
$filter = '';

if ($biblio_initial_filter != ''){
    if ($user_filter != ''){
        $filter = $biblio_initial_filter . ' AND ' . $user_filter;
    }else{
        $filter = $biblio_initial_filter;
    }
}else{
    $filter = $user_filter;
}
$start = ($page * $count) - $count;

$biblio_service_request = $biblio_service_url . 'api/bibliographic/search/?q=' . urlencode($query) . '&fq=' . urlencode($filter) . '&start=' . $start . '&count=' . $count . '&lang=' . $lang;

$filter_list = explode(";", $biblio_config['available_filter']);

foreach ($filter_list as $filter_field){
    $biblio_service_request.= "&facet.field=" . urlencode($filter_field);
}

if ( $user_filter != '' ) {
    $user_filter_list = preg_split("/ AND /", $user_filter);
    $applied_filter_list = array();
    foreach($user_filter_list as $filter){
        preg_match('/([a-z_]+):(.+)/',$filter, $filter_parts);
        if ($filter_parts){
            // convert to internal format
            $applied_filter_list[$filter_parts[1]][] = str_replace('"', '', $filter_parts[2]);
        }
    }
}

//echo $biblio_service_request;

$response = @file_get_contents($biblio_service_request);
if ($response){
    $response_json = json_decode($response);
    //echo "<pre>"; print_r($response_json); echo "</pre>";
    $total = $response_json->diaServerResponse[0]->response->numFound;
    $start = $response_json->diaServerResponse[0]->response->start;
    $docs_list = $response_json->diaServerResponse[0]->response->docs;
    $facet_list = (array) $response_json->diaServerResponse[0]->facet_counts->facet_fields;
}

$params  = !empty($format) ? '&format=' . $format : '';
$params .= $count != 2 ? '&count=' . $count : '';
$params .= !empty($_GET['sort']) ? '&sort=' . sanitize_text_field($_GET['sort']) : '';

$page_url_params = real_site_url($biblio_plugin_slug) . '?q=' . urlencode($query) . '&filter=' . urlencode($user_filter) . $params;
$feed_url = real_site_url($biblio_plugin_slug) . 'bibliographic-feed?q=' . urlencode($query) . '&filter=' . urlencode($filter);

$pages = new Paginator($total, $start);
$pages->paginate($page_url_params);

$home_url = isset($biblio_config['home_url_' . $lang]) ? $biblio_config['home_url_' . $lang] : real_site_url();
$plugin_breadcrumb = isset($biblio_config['plugin_title_' . $lang]) ? $biblio_config['plugin_title_' . $lang] : $biblio_config['plugin_title'];

?>

<?php get_header('biblio');?>

    <div id="content" class="row-fluid">
        <div class="ajusta2">
            <div class="row-fluid breadcrumb">
                <a href="<?php echo $home_url ?>"><?php _e('Home','biblio'); ?></a> >
                <?php if ($query == '' && $filter == ''): ?>
                    <?php echo $plugin_breadcrumb ?>
                <?php else: ?>
                    <a href="<?php echo real_site_url($biblio_plugin_slug); ?>"><?php echo $plugin_breadcrumb ?> </a> >
                    <?php _e('Search result', 'biblio') ?>
                <?php endif; ?>
            </div>


            <!-- Start sidebar bibliographic-header -->
            <div class="row-fluid">
                <?php dynamic_sidebar('biblio-header');?>
            </div>
            <div class="spacer"></div>
            <!-- end sidebar bibliographic-header -->

            <section class="header-search">
                <form role="search" method="get" name="searchForm" id="searchForm" action="<?php echo real_site_url($biblio_plugin_slug); ?>">
                    <input type="hidden" name="lang" id="lang" value="<?php echo $lang; ?>">
                    <input type="hidden" name="sort" id="sort" value="<?php echo sanitize_text_field($_GET['sort']); ?>">
                    <input type="hidden" name="format" id="format" value="<?php echo $format ? $format : 'summary'; ?>">
                    <input type="hidden" name="count" id="count" value="<?php echo $count; ?>">
                    <input type="hidden" name="page" id="page" value="1">
                    <input value='<?php echo $query; ?>' name="q" class="input-search" id="s" type="text" placeholder="<?php _e('Enter one or more words', 'biblio'); ?>">
                    <input id="searchsubmit" value="<?php _e('Search', 'biblio'); ?>" type="submit">
                    <a href="#" title="<?php _e('Tip! You can do your search using boolean operators.', 'biblio'); ?>" class="help ketchup tooltip"><i class="fa fa-question-circle fa-2x"></i></a>
                </form>
                <div class="pull-right rss">
                    <a href="<?php echo $feed_url ?>" target="blank"><img src="<?php echo BIBLIOGRAPHIC_PLUGIN_URL; ?>template/images/icon_rss.png" ></a>
                </div>
            </section>
            <div class="content-area result-list">
                <section id="conteudo">
                    <?php if ( isset($total) && strval($total) == 0 ) :?>
                        <h1 class="h1-header"><?php _e('No results found','biblio'); ?></h1>
                    <?php else :?>
                        <header class="row-fluid border-bottom">
                            <?php if ( ( $query != '' || $user_filter != '' ) && strval($total) > 0) :?>
                                <h1 class="h1-header"><?php _e('Results', 'biblio'); echo ': ' . $total ?></h1>
                            <?php else: ?>
                                <h1 class="h1-header"><?php _e('Total', 'biblio'); echo ': ' . $total ?></h1>
                            <?php endif; ?>
                        </header>
                        <div class="row-fluid">

                                <?php foreach ( $docs_list as $position => $docs) { $position++; ?>
                                    <article class="conteudo-loop">
                                        <h2 class="h2-loop-tit">
                                            <a href="<?php echo real_site_url($biblio_plugin_slug); ?>resource/?id=<?php echo $docs->id; ?>"><?php echo $docs->reference_title[0]; ?></a>
                                            <?php if ( $docs->english_title ): ?>
                                                <div class="altLang"><?php echo $docs->english_title; ?></div>
                                            <?php endif; ?>
                                        </h2>

                                        <?php if ( $docs->author ): ?>
                                            <div class="row-fluid authors">
                                                <?php foreach ( $docs->author as $index => $author ):
                                                    echo "<a href='" . real_site_url($biblio_plugin_slug) . "?filter=author:\"" . $author . "\"'>" . $author . "</a>";
                                                    echo count($docs->author)-1 != $index ? '; ' : '.';
                                                endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $docs->journal ): ?>
                                            <div class="row-fluid">
                                                <?php
                                                    echo "<a href='" . real_site_url($biblio_plugin_slug) . "?filter=journal:\"" . $docs->journal[0] . "\"'>" . $docs->journal[0] . "</a>";
                                                    if ( $docs->reference_source ):
                                                        echo substr($docs->reference_source, strpos($docs->reference_source, ';'), 100);
                                                    endif;
                                                ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $docs->reference_abstract ): ?>
                                            <div class="row-fluid">
                                                <?php
                                                    $ab_clean = str_replace(array("\\r\\n", "\\t", "\\r", "\\n"), '' ,$docs->reference_abstract[0]);
                                                    echo substr($ab_clean, 0, 305) . '...';
                                                ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($docs->mh ) : ?>
                                            <div class="row-fluid subjects">
                                                <strong><i class="fa fa-tags" aria-hidden="true"></i></strong>
                                                <?php
                                                    $subjects = array();
                                                    foreach ( $docs->mh as $index => $subject ):
                                                        echo "<a href='" . real_site_url($biblio_plugin_slug) . "?q=mh:\"" . $subject . "\"'>" . $subject . "</a>";
                                                        echo $index != count($docs->mh)-1 ? ', ' : '';
                                                    endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ( $docs->link ) : ?>
                                            <div class="row-fluid">
                                                <?php if ( $alternative_links && count($docs->link) > 1): ?>
                                                    <?php foreach ($docs->link as $index => $link): ?>
                                                        <span class="more">
                                                            <a href="<?php echo $link ?>" target="_blank">
                                                                <i class="fa fa-file" aria-hidden="true"> </i>
                                                                <?php ( ($index == 0) ? _e('Fulltext (primary link)','biblio') : _e('Fulltext (alternative link)','biblio')); ?>
                                                            </a>
                                                        </span>&nbsp;&nbsp;&nbsp;
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <span class="more">
                                                        <a href="<?php echo $docs->link[0] ?>" target="_blank">
                                                            <i class="fa fa-file" aria-hidden="true"> </i> <?php _e('Fulltext','biblio'); ?>
                                                        </a>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </article>
                                <?php } ?>

                        </div>
                        <div class="row-fluid">
                            <?php echo $pages->display_pages(); ?>
                        </div>
                    <?php endif; ?>
                </section>
                <aside id="sidebar">

                    <?php dynamic_sidebar('biblio-home');?>

                    <?php if (strval($total) > 0) :?>
                        <div id="filter-link" style="display: none">
                            <div class="mobile-menu" onclick="animateMenu(this)">
                                <a href="javascript:showHideFilters()">
                                    <div class="menu-bar">
                                        <div class="bar1"></div>
                                        <div class="bar2"></div>
                                        <div class="bar3"></div>
                                    </div>
                                    <div class="menu-item">
                                        <?php _e('Filters','biblio') ?>
                                    </div>
                                </a>
                           </div>
                        </div>
                        <div id="filters">

                            <?php if ($applied_filter_list) :?>
                                <section class="row-fluid widget_categories">
                                    <header class="row-fluid marginbottom15">
                                        <h1 class="h1-header"><?php echo _e('Selected filters', 'biblio') ?></h1>
                                    </header>
                                    <form method="get" name="searchFilter" id="formFilters" action="<?php echo real_site_url($biblio_plugin_slug); ?>">
                                        <input type="hidden" name="lang" id="lang" value="<?php echo $lang; ?>">
                                        <input type="hidden" name="sort" id="sort" value="<?php echo $sort; ?>">
                                        <input type="hidden" name="format" id="format" value="<?php echo $format; ?>">
                                        <input type="hidden" name="count" id="count" value="<?php echo $count; ?>">
                                        <input type="hidden" name="q" id="query" value="<?php echo $query; ?>" >
                                        <input type="hidden" name="filter" id="filter" value="" >

                                        <?php foreach ( $applied_filter_list as $filter => $filter_values ) :?>
                                            <ul>
                                            <strong>
                                                <?php
                                                    $filter_field = ($filter == 'mj' ? 'descriptor_filter' : $filter);
                                                    echo translate_label($biblio_texts, $filter_field, 'filter')
                                                ?>
                                            </strong>

                                            <?php foreach ( $filter_values as $value ) :?>
                                                <input type="hidden" name="apply_filter" class="apply_filter"
                                                        id="<?php echo md5($value) ?>" value='<?php echo $filter . ':"' . $value . '"'; ?>' >
                                                <li>
                                                    <span class="filter-item">
                                                        <?php
                                                            if (strpos($value, '^') !== false){
                                                                echo print_lang_value($value, $site_language);
                                                            }elseif (array_key_exists($filter, $biblio_texts)){
                                                                echo translate_label($biblio_texts, $value, $filter);
                                                            }else{
                                                                echo $value;
                                                            }
                                                        ?>
                                                    </span>
                                                    <span class="filter-item-del">
                                                        <a href="javascript:remove_filter('<?php echo md5($value) ?>')">
                                                            <img src="<?php echo BIBLIOGRAPHIC_PLUGIN_URL; ?>template/images/del.png">
                                                        </a>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        <?php endforeach; ?>
                                    </form>
                                </section>
                            <?php endif; ?>

                            <?php
                                foreach($filter_list as $filter_field) {
                            ?>
                                <?php if ($facet_list[$filter_field] ): ?>
                                    <section class="row-fluid widget_categories">
                                        <header class="row-fluid border-bottom marginbottom15">
                                            <h1 class="h1-header"><?php echo $biblio_texts['filter'][$filter_field]; ?></h1>
                                        </header>
                                        <ul>
                                            <?php foreach ( $facet_list[$filter_field] as $filter_item ) { ?>
                                                <?php
                                                    $filter_value = $filter_item[0];
                                                    $filter_count = $filter_item[1];

                                                    if ($filter_field == 'descriptor_filter') {
                                                        $filter_field = 'mj';
                                                    }
                                                ?>
                                                <?php if ( 'mj' != $filter_field || filter_var($filter_value, FILTER_VALIDATE_INT) === false) : ?>
                                                    <li class="cat-item">
                                                        <?php
                                                            $filter_link = '?';
                                                            if ($query != ''){
                                                                $filter_link .= 'q=' . $query . '&';
                                                            }
                                                            $filter_link .= 'filter=' . $filter_field . ':"' . $filter_value . '"';
                                                            if ($user_filter != ''){
                                                                $filter_link .= ' AND ' . $user_filter ;
                                                            }
                                                        ?>
                                                        <?php if ( strpos($filter_value, '^') !== false ): ?>
                                                            <a href='<?php echo $filter_link; ?>'><?php print_lang_value($filter_value, $site_language); ?></a>
                                                        <?php elseif ( array_key_exists($filter_field, $biblio_texts) ): ?>
                                                            <a href='<?php echo $filter_link; ?>'><?php  echo translate_label($biblio_texts, $filter_value, $filter_field); ?></a>
                                                        <?php else: ?>
                                                            <a href='<?php echo $filter_link; ?>'><?php echo $filter_value; ?></a>
                                                        <?php endif; ?>
                                                        <span class="cat-item-count"><?php echo $filter_count; ?></span>
                                                    </li>
                                                <?php endif; ?>
                                            <?php } ?>
                                        </ul>
                                    </section>
                                <?php endif; ?>
                            <?php } ?>

                        </div> <!-- close DIV.filters -->
                    <?php endif; ?>
                </aside>
                <div class="spacer"></div>
            </div> <!-- close DIV.result-area -->
        </div> <!-- close DIV.ajusta2 -->
    </div>
<?php get_footer(); ?>
