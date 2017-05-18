<?php
/*
Template Name: Bibliographic Home
*/
global $biblio_service_url, $biblio_plugin_slug, $biblio_plugin_title, $biblio_texts;

require_once(BIBLIOGRAPHIC_PLUGIN_PATH . '/lib/Paginator.php');

$order = array(
        'RELEVANCE' => 'score desc',
        'YEAR_ASC'  => 'publication_year asc',
        'YEAR_DESC' => 'publication_year desc'
    );

$biblio_config         = get_option('biblio_config');
$biblio_initial_filter = $biblio_config['initial_filter'];
$biblio_addthis_id     = $biblio_config['addthis_profile_id'];

$site_language = strtolower(get_bloginfo('language'));
$lang = substr($site_language,0,2);

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

$biblio_service_request = $biblio_service_url . 'api/bibliographic/search/?q=' . urlencode($query) . '&fq=' . urlencode($filter) . '&start=' . $start . '&count=' . $count . '&sort=' . urlencode($sort) . '&lang=' . $lang;

//print $biblio_service_request;

$response = @file_get_contents($biblio_service_request);
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

$lang_translate = array('pt' => __('Portuguese'), 'es' => _('Spanish'), 'en' => _('English'), 'fr' => _('French'));

#print_r($docs_list);

$params  = !empty($format) ? '&format=' . $format : '';
$params .= $count != 2 ? '&count=' . $count : '';
$params .= !empty($_GET['sort']) ? '&sort=' . $_GET['sort'] : '';

$page_url_params = real_site_url($biblio_plugin_slug) . '?q=' . urlencode($query) . '&filter=' . urlencode($filter) . $params;
$feed_url = real_site_url($biblio_plugin_slug) . 'bibliographic-feed?q=' . urlencode($query) . '&filter=' . urlencode($filter);

$pages = new Paginator($total, $start);
$pages->paginate($page_url_params);

?>

<?php get_header('biblio');?>
    <div class="row-fluid breadcrumb">
        <a href="<?php echo real_site_url(); ?>"><?php _e('Home','biblio'); ?></a> >
        <?php if ($query == '' && $filter == ''): ?>
            <?php echo $biblio_plugin_title ?>
        <?php else: ?>
            <a href="<?php echo real_site_url($biblio_plugin_slug); ?>"><?php echo $biblio_plugin_title ?> </a> >
            <?php _e('Search result', 'biblio') ?>
        <?php endif; ?>
    </div>

    <div id="content" class="row-fluid">
        <div class="ajusta2">
            <section class="header-search">
                <form role="search" method="get" name="searchForm" id="searchForm" action="<?php echo real_site_url($biblio_plugin_slug); ?>">
                    <input type="hidden" name="lang" id="lang" value="<?php echo $lang; ?>">
                    <input type="hidden" name="sort" id="sort" value="<?php echo $_GET['sort']; ?>">
                    <input type="hidden" name="format" id="format" value="<?php echo $format ? $format : 'summary'; ?>">
                    <input type="hidden" name="count" id="count" value="<?php echo $count; ?>">
                    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
                    <input value='<?php echo $query; ?>' name="q" class="input-search" id="s" type="text" placeholder="<?php _e('Enter one or more words', 'biblio'); ?>">
                    <input id="searchsubmit" value="<?php _e('Search', 'biblio'); ?>" type="submit">
                    <a href="#" title="<?php _e('Tip! You can do your search using boolean operators.', 'biblio'); ?>" class="help ketchup tooltip"><i class="fa fa-question-circle fa-2x"></i></a>
                </form>
                <div class="pull-right">
                    <a href="<?php echo $feed_url ?>" target="blank"><img src="<?php echo BIBLIOGRAPHIC_PLUGIN_URL; ?>template/images/icon_rss.png" ></a>
                </div>
            </section>
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
                                                    echo substr($docs->reference_source[0], strpos($docs->reference_source[0], ';'), 100);
                                                endif;
                                            ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( $docs->reference_abstract ): ?>
                                        <div class="row-fluid">
                                            <?php echo substr($docs->reference_abstract[0], 0, 300) . '...' ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($docs->descriptor ) : ?>
                                        <div class="row-fluid subjects">
                                            <strong><i class="fa fa-tags" aria-hidden="true"></i></strong>
                                            <?php
                                                $subjects = array();
                                                foreach ( $docs->descriptor as $index => $subject ):
                                                    echo "<a href='" . real_site_url($biblio_plugin_slug) . "?filter=descriptor:\"" . $subject . "\"'>" . $subject . "</a>";
                                                    echo $index != count($docs->descriptor)-1 ? ', ' : '';
                                                endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ( $docs->link ) : ?>
                                        <div class="row-fluid">
                                            <?php foreach ($docs->link as $link): ?>
                                                <span class="more">
                                                    <a href="<?php echo $link ?>" target="_blank">
                                                        <i class="fa fa-file" aria-hidden="true"></i> <?php _e('Fulltext','biblio'); ?>
                                                    </a>
                                                </span>&nbsp;&nbsp;&nbsp;
                                            <?php endforeach; ?>
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
                    <?php if ( in_array('main_subject', $biblio_config['available_filter']) && $descriptor_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Main subject','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $descriptor_list as $index => $descriptor ) { $index++; ?>
                                    <li class="cat-item">
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
                        </section>
                    <?php endif; ?>
                    <?php if ( in_array('publication_type', $biblio_config['available_filter']) && $type_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Publication type','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $type_list as $type ) { ?>
                                    <li class="cat-item">
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
                                        <a href='<?php echo $filter_link; ?>'><?php echo translate_label($biblio_texts, $type[0], 'publication_type') ?></a>
                                        <span class="cat-item-count"><?php echo $type[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                    <?php if ( in_array('database', $biblio_config['available_filter']) && $database_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Database','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $database_list as $db ) { ?>
                                    <li class="cat-item">
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
                        </section>
                    <?php endif; ?>
                    <?php if ( in_array('publication_country', $biblio_config['available_filter']) && $cp_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Publication country','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $cp_list as $cp ) { ?>
                                    <li class="cat-item">
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
                        </section>
                    <?php endif; ?>
                    <?php if ( in_array('limit', $biblio_config['available_filter']) && $limit_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Limits','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $limit_list as $limit ) { ?>
                                    <li class="cat-item">
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
                        </section>
                    <?php endif; ?>
                    <?php if ( in_array('language', $biblio_config['available_filter']) && $language_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Language','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $language_list as $lang ) { ?>
                                    <li class="cat-item">
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
                                        <a href='<?php echo $filter_link; ?>'><?php echo $lang_translate[$lang[0]]; ?></a>
                                        <span class="cat-item-count"><?php echo $lang[1]; ?></span>
                                    </li>
                                <?php } ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                    <?php if ( in_array('journal', $biblio_config['available_filter']) && $journal_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Journal','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $journal_list as $journal ) { ?>
                                    <li class="cat-item">
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
                        </section>
                    <?php endif; ?>
                    <?php if ( in_array('year', $biblio_config['available_filter']) && $year_list ): ?>
                        <section class="row-fluid widget_categories">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('Year','biblio'); ?></h1>
                            </header>
                            <ul>
                                <?php foreach ( $year_list as $year ) { ?>
                                    <li class="cat-item">
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
                        </section>
                    <?php endif; ?>
                <? endif; ?>
            </aside>
            <div class="spacer"></div>
        </div>
    </div>
<?php get_footer(); ?>
