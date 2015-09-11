<?php
/*
Template Name: LILDBI-WEB Home
*/

require_once(LILDBI_PLUGIN_PATH . '/lib/Paginator.php');

global $lildbi_service_url, $lildbi_plugin_slug;

$order = array(
        'RELEVANCE' => 'score desc',
        'YEAR_ASC'  => 'publication_year asc',
        'YEAR_DESC' => 'publication_year desc'
    );

$lildbi_config         = get_option('lildbi_config');
$lildbi_initial_filter = $lildbi_config['initial_filter'];
$lildbi_addthis_id     = $lildbi_config['addthis_profile_id'];
$lildbi_about          = $lildbi_config['about'];
$lildbi_tutorials      = $lildbi_config['tutorials'];

$site_language = strtolower(get_bloginfo('language'));
$lang_dir = substr($site_language,0,2);

// set query using default param q (query) or s (wordpress search) or newexpr (metaiah)
$query = $_GET['s'] . $_GET['q'];
$query = stripslashes( trim($query) );

$user_filter = stripslashes($_GET['filter']);
$page   = ( !empty($_GET['page']) ? $_GET['page'] : 1 );
$format = ( !empty($_GET['format']) ? $_GET['format'] : '' );
$sort   = ( !empty($_GET['sort']) ? $order[$_GET['sort']] : 'created_date desc' );
$count  = ( !empty($_GET['count']) ? $_GET['count'] : 10 );
$total  = 0;
$filter = '';

if ($lildbi_initial_filter != ''){
    if ($user_filter != ''){
        $filter = $lildbi_initial_filter . ' AND ' . $user_filter;
    }else{
        $filter = $lildbi_initial_filter;
    }
}else{
    $filter = $user_filter;
}
$start = ($page * $count) - $count;

$lildbi_service_request = $lildbi_service_url . 'api/bibliographic/search/?q=' . urlencode($query) . '&fq=' . urlencode($filter) . '&start=' . $start . '&count=' . $count . '&sort=' . urlencode($sort) . '&lang=' . $lang_dir;

//print $lildbi_service_request;

$response = @file_get_contents($lildbi_service_request);
if ($response){
    $response_json = json_decode($response);
    //echo "<pre>"; print_r($response_json); echo "</pre>";
    $total = $response_json->diaServerResponse[0]->response->numFound;
    $start = $response_json->diaServerResponse[0]->response->start;
    $docs_list = $response_json->diaServerResponse[0]->response->docs;
    $descriptor_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->descriptor_filter;
    $type_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->publication_type;
    $database_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->database;
    $cp_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->publication_country;
    $limit_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->check_tags;
    $language_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->publication_language;
    $journal_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->journal;
    $year_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->publication_year;
}

$params  = !empty($format) ? '&format=' . $format : '';
$params .= $count != 2 ? '&count=' . $count : '';
$params .= !empty($_GET['sort']) ? '&sort=' . $_GET['sort'] : '';

$page_url_params = real_site_url($lildbi_plugin_slug) . '?q=' . urlencode($query) . '&filter=' . urlencode($filter) . $params;
$feed_url = real_site_url($lildbi_plugin_slug) . 'lildbi-feed?q=' . urlencode($query) . '&filter=' . urlencode($filter);

$pages = new Paginator($total, $start);
$pages->paginate($page_url_params);

?>

<?php get_header('lildbi');?>
    <div id="content" class="row-fluid">
        <div class="header-menu">
            <nav role="navigation">
                <div class="menu">
                    <ul id="prime_nav" class="menu">
                        <li><a href="<?php echo real_site_url(); ?>"><span><?php _e('Home','lildbi'); ?></span></a></li>
                        <li><a href="<?php echo $lildbi_about; ?>"><span><?php _e('About LILDBI-WEB', 'lildbi') ?></span></a></li>
                        <li><a href="<?php echo $lildbi_tutorials; ?>"><span><?php _e('Tutorials','lildbi'); ?></span></a></li>
                    </ul>
                </div>
            </nav>
            <div class="spacer"></div>
        </div>
        <div class="ajusta2">
            <section class="header-search">
                <form role="search" method="get" name="searchForm" id="searchForm" action="<?php echo real_site_url($lildbi_plugin_slug); ?>">
                    <input type="hidden" name="lang" id="lang" value="<?php echo $lang_dir; ?>">
                    <input type="hidden" name="sort" id="sort" value="<?php echo $_GET['sort']; ?>">
                    <input type="hidden" name="format" id="format" value="<?php echo $format ? $format : 'summary'; ?>">
                    <input type="hidden" name="count" id="count" value="<?php echo $count; ?>">
                    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
                    <input value='<?php echo $query; ?>' name="q" class="input-search" id="s" type="text" placeholder="<?php _e('Search Documents', 'lildbi'); ?>">
                    <input id="searchsubmit" value="<?php _e('Search', 'lildbi'); ?>" type="submit">
                    <a href="#" title="<?php _e('Tip! You can do your search using boolean operators.', 'lildbi'); ?>" class="help ketchup tooltip"><i class="fa fa-question-circle fa-2x"></i></a>
                </form>
            </section>
            <section id="conteudo">
                <?php if ( isset($total) && strval($total) == 0 ) :?>
                    <h1 class="h1-header"><?php _e('No results found','lildbi'); ?></h1>
                <?php else :?>
                    <header class="row-fluid border-bottom">
                        <?php if ( ( $query != '' || $user_filter != '' ) && strval($total) > 0) :?>
                            <h1 class="h1-header"><span class="breadcrumb-home"><a href="<?php echo real_site_url($lildbi_plugin_slug); ?>"><?php _e('HOME','lildbi'); ?></a></span> / <?php echo ( strlen($query) > 35 ? substr($query,0,35) . '...' : $query ); ?></h1>
                            <div class="count-docs pull-right"><?php echo $total; ?> <?php echo ($total == 1) ? __(' document') : __(' documents'); ?></div>
                        <?php else: ?>
                            <h1 class="h1-header"><?php _e('Recent documents', 'lildbi') ?></h1>
                        <?php endif; ?>
                        <div class="spacer"></div>
                        <div class="resultsBar">
                            <div class="formset">
                                <label for="output" class="hide"><?php _e('Presentation format', 'lildbi') ?></label>
                                <select name="output" id="output" class="inputText" onchange="javascript:change_format(this);">
                                    <option value=""><?php _e('Presentation format', 'lildbi') ?></option>
                                    <option value="summary" <?php selected( $format, 'summary' ); ?>><?php _e('Short', 'lildbi') ?></option>
                                    <option value="abstract" <?php selected( $format, 'abstract' ); ?>><?php _e('Detailed', 'lildbi') ?></option>
                                </select>
                            </div>
                            <div class="formset">
                                <label for="order" class="hide"><?php _e('Order by', 'lildbi') ?></label>
                                <select name="order" id="order" class="inputText" onchange="javascript:change_sort(this);">
                                    <option value=""><?php _e('Order by', 'lildbi') ?></option>
                                    <option value="RELEVANCE" <?php selected( $sort, 'score desc' ); ?>><?php _e('Relevance', 'lildbi') ?></option>
                                    <option value="YEAR_DESC" <?php selected( $sort, 'publication_year desc' ); ?>><?php _e('Descending year', 'lildbi') ?></option>
                                    <option value="YEAR_ASC" <?php selected( $sort, 'publication_year asc' ); ?>><?php _e('Ascending year', 'lildbi') ?></option>
                                </select>
                            </div>
                            <div class="formset">
                                <label for="per_page" class="hide"><?php _e('Documents per page', 'lildbi') ?></label>
                                <select name="per_page" id="per_page" onchange="change_count(this);">
                                    <option value=""><?php _e('Documents per page', 'lildbi') ?></option>
                                    <option value="10" <?php selected( $count, '10' ); ?>>10</option>
                                    <option value="20" <?php selected( $count, '20' ); ?>>20</option>
                                    <option value="30" <?php selected( $count, '30' ); ?>>30</option>
                                    <option value="50" <?php selected( $count, '50' ); ?>>50</option>
                                    <option value="100" <?php selected( $count, '100' ); ?>>100</option>
                                </select>
                            </div>
                            <div class="rss_feed">
    				            <a href="<?php echo $feed_url ?>" target="blank"><img src="<?php echo LILDBI_PLUGIN_URL; ?>template/images/icon_RSS.gif" class="rss_feed" ></a>
                            </div>
                            <!-- AddThis Button BEGIN -->
                            <div class="addthis_toolbox addthis_default_style">
                                <span class="addthis_separator">|</span>
                                <a class="addthis_button_facebook"></a>
                                <a class="addthis_button_delicious"></a>
                                <a class="addthis_button_google_plusone_share"></a>
                                <a class="addthis_button_favorites"></a>
                                <a class="addthis_button_compact"></a>
                            </div>
                            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $lildbi_addthis_id; ?>"></script>
                            <!-- AddThis Button END -->
                        </div>
                    </header>
                    <div class="row-fluid">
                        <ol class="doc-loop">
                            <?php foreach ( $docs_list as $position => $docs) { $position++; ?>
                                <li>
                                    <h2 class="h2-loop-tit">
                                        <div class="position"><?php echo $position + $start; ?>. </div>
                                        <a href="<?php echo real_site_url($lildbi_plugin_slug); ?>resource/<?php echo $docs->django_id; ?>"><?php echo $docs->reference_title[0]; ?></a>
                                        <?php foreach ( $docs->reference_title as $index => $title ): ?>
                                            <?php if ( $index != 0 ): ?>
                                                <span class="altLang"><?php echo $title; ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </h2>

                                    <?php if ( $docs->author ): ?>
                                        <p class="row-fluid">
                                            <?php foreach ( $docs->author as $index => $author ):
                                                echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=author:\"" . $author . "\"'>" . $author . "</a>";
                                                echo count($docs->author)-1 != $index ? '; ' : '.';
                                            endforeach; ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ( $docs->journal ): ?>
                                        <p class="row-fluid">
                                            <?php
                                                echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=journal:\"" . $docs->journal[0] . "\"'>" . $docs->journal[0] . "</a>";
                                                if ( $docs->reference_source ):
                                                    echo substr($docs->reference_source, strpos($docs->reference_source, ';'), 100);
                                                endif;
                                            ?>
                                        </p>
                                    <?php endif; ?>

                                    <p class="row-fluid">
                                        <?php
                                            if ( $docs->publication_type ):
                                                echo ucfirst( $docs->publication_type[0] );
                                                if ( $docs->publication_language ){
                                                    echo __(' in ') . strtoupper(implode(', ', $docs->publication_language));
                                                }
                                                echo ' | ';
                                            endif;
                                            if ( $docs->database ) echo $docs->database[0] . ' | ';
                                            if ( $docs->django_id ) echo 'ID: ' . $docs->django_id;
                                        ?>
                                        <br/>
                                    </p>

                                    <?php if ( $docs->link ) : ?>
                                        <p class="row-fluid">
                                            <a href="<?php echo $docs->link[0]; ?>">
                                                <em class="fa fa-file-text-o"></em> <?php _e('Full Text','lildbi'); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ( !empty( $format ) && $format == 'abstract' ): ?>
                                        <?php if ( $docs->reference_abstract ): ?>
                                            <p class="row-fluid abstract">
                                                <strong><?php _e('ABSTRACT','lildbi'); ?></strong>
                                                <?php foreach ( $docs->reference_abstract as $index => $abs ): ?>
                                                    <?php $class = $index != 0 ? 'altLang' : ''; ?>
                                                    <div class="abstract-version <?php echo $class; ?>">
                            							<?php echo ( strlen($abs) > 400 ? substr($abs,0,400) . '...' : $abs); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($docs->descriptor ) : ?>
                                            <p class="row-fluid subjects">
                                                <strong><?php _e('SUBJECTS','lildbi'); ?></strong>
                                                <?php
                                                    $subjects = array();
                                                    foreach ( $docs->descriptor as $index => $subject ):
                                                        echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=descriptor:\"" . $subject . "\"'>" . $subject . "</a>";
                                                        echo $index != count($docs->descriptor)-1 ? ', ' : '';
                                                    endforeach; ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                            <?php } ?>
                        </ol>
                    </div>
                    <div class="row-fluid">
                        <?php echo $pages->display_pages(); ?>
                    </div>
                <?php endif; ?>
            </section>
            <aside id="sidebar">

                <?php dynamic_sidebar('lildbi-home');?>

                <?php if (strval($total) > 0) :?>
                    <?php if ( $descriptor_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Main Subject','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $descriptor_list as $index => $descriptor ) { $index++; ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=descriptor:"' . $descriptor[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo $descriptor[0]; ?></a>
                                        <span class="cat-item-count"><?php echo $descriptor[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($descriptor_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    <?php if ( $type_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Publication Type','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $type_list as $type ) { ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=publication_type:"' . $type[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo ucfirst( $type[0] ); ?></a>
                                        <span class="cat-item-count"><?php echo $type[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($type_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    <?php if ( $database_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Database','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $database_list as $db ) { ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=database:"' . $db[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo $db[0]; ?></a>
                                        <span class="cat-item-count"><?php echo $db[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($database_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    <?php if ( $cp_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Publication Country','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $cp_list as $cp ) { ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=publication_country:"' . $cp[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo $cp[0]; ?></a>
                                        <span class="cat-item-count"><?php echo $cp[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($cp_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    <?php if ( $limit_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Limits','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $limit_list as $limit ) { ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=check_tags:"' . $limit[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo $limit[0]; ?></a>
                                        <span class="cat-item-count"><?php echo $limit[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($limit_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    <?php if ( $language_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Language','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $language_list as $lang ) { ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=publication_language:"' . $lang[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo strtoupper($lang[0]); ?></a>
                                        <span class="cat-item-count"><?php echo $lang[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($language_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    <?php if ( $journal_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Journal','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $journal_list as $journal ) { ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=journal:"' . $journal[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo $journal[0]; ?></a>
                                        <span class="cat-item-count"><?php echo $journal[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($journal_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                    <?php if ( $year_list ): ?>
                        <section class="row-fluid marginbottom25 widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Year','lildbi'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $year_list as $year ) { ?>
                                    <?php $class = ($index > 10) ? 'hide' : ''; ?>
                                    <li class="cat-item <?php echo $class; ?>">
                                        <?php
                                            $filter_link = '?';
                                            if ($query != ''){
                                                $filter_link .= 'q=' . $query . '&';
                                            }
                                            $filter_link .= 'filter=publication_year:"' . $year[0] . '"';
                                            if ($user_filter != ''){
                                                $filter_link .= ' AND ' . $user_filter ;
                                            }
                                        ?>
                                        <a href='<?php echo $filter_link; ?>'><?php echo $year[0]; ?></a>
                                        <span class="cat-item-count"><?php echo $year[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                            <?php if ( count($year_list) > 10 ):?>
                                <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                <? endif; ?>
            </aside>
            <div class="spacer"></div>
        </div>
    </div>
<?php get_footer(); ?>
