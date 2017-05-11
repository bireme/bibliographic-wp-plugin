<?php
/*
Template Name: Bibliographic Detail
*/

global $biblio_service_url, $biblio_plugin_slug;

$biblio_config         = get_option('biblio_config');
$biblio_initial_filter = $biblio_config['initial_filter'];
$biblio_addthis_id     = $biblio_config['addthis_profile_id'];
$biblio_about          = $biblio_config['about'];
$biblio_tutorials      = $biblio_config['tutorials'];

$referer = wp_get_referer();
$path = parse_url($referer);
if ( array_key_exists( 'query', $path ) ) {
    $path = parse_str($path['query'], $output);
    //echo "<pre>"; print_r($output); echo "</pre>";
    if ( array_key_exists( 'q', $output ) && !empty( $output['q'] ) ) {
        $query = $output['q'];
        $q = ( strlen($output['q']) > 10 ? substr($output['q'],0,10) . '...' : $output['q'] );
        $ref = ' / <a href="'. $referer . '">' . $q . '</a>';
    }
}

$filter = '';
$user_filter = stripslashes($output['filter']);
if ($biblio_initial_filter != ''){
    if ($user_filter != ''){
        $filter = $biblio_initial_filter . ' AND ' . $user_filter;
    }else{
        $filter = $biblio_initial_filter;
    }
}else{
    $filter = $user_filter;
}

$request_uri   = $_SERVER["REQUEST_URI"];
$request_parts = explode('/', $request_uri);
$resource_id   = $_GET['id'];

$site_language = strtolower(get_bloginfo('language'));
$lang_dir = substr($site_language,0,2);

$biblio_service_request = $biblio_service_url . 'api/bibliographic/search/?id=' . $resource_id . '&op=related&lang=' . $lang_dir;

$response = @file_get_contents($biblio_service_request);

if ($response){
    $response_json = json_decode($response);
    //echo "<pre>"; print_r($response_json); echo "</pre>";
    $resource = $response_json->diaServerResponse[0]->match->docs[0];
    //$related_list = $response_json->diaServerResponse[0]->response->docs;
}

$feed_url = real_site_url($biblio_plugin_slug) . 'biblio-feed?q=' . urlencode($query) . '&filter=' . urlencode($filter);

?>

<?php get_header('biblio');?>
    <div class="row-fluid breadcrumb">
        <a href="<?php echo real_site_url(); ?>"><?php _e('Home','biblio'); ?></a> >
        <a href="<?php echo real_site_url($biblio_plugin_slug); ?>"><?php _e('Bibliographic records', 'biblio') ?> </a> >
        <?php echo ( strlen($resource->reference_title[0]) > 90 ) ? substr($resource->reference_title[0],0,90) . '...' : $resource->reference_title[0]; ?>
    </div>

    <div id="content" class="row-fluid">
        <div class="ajusta2">
            <section class="header-search">
                <form role="search" method="get" name="searchForm" id="searchForm" action="<?php echo real_site_url($biblio_plugin_slug); ?>">
                    <input type="hidden" name="lang" id="lang" value="<?php echo $lang_dir; ?>">
                    <input type="hidden" name="sort" id="sort" value="">
                    <input type="hidden" name="format" id="format" value="summary">
                    <input type="hidden" name="count" id="count" value="10">
                    <input type="hidden" name="page" id="page" value="1">
                    <input value="" name="q" class="input-search" id="s" type="text" placeholder="<?php _e('Enter one or more words', 'biblio'); ?>">
                    <input id="searchsubmit" value="<?php _e('Search', 'biblio'); ?>" type="submit">
                    <a href="#" title="<?php _e('Tip! You can do your search using boolean operators.', 'biblio'); ?>" class="help ketchup tooltip"><i class="fa fa-question-circle fa-2x"></i></a>
                </form>
            </section>
            <section id="conteudo">
                <div class="row-fluid">
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style">
                        <a class="addthis_button_facebook"></a>
                        <a class="addthis_button_delicious"></a>
                        <a class="addthis_button_google_plusone_share"></a>
                        <a class="addthis_button_favorites"></a>
                        <a class="addthis_button_compact"></a>
                    </div>
                    <script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
                    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $biblio_addthis_id; ?>"></script>
                    <!-- AddThis Button END -->
                </div>
                <div class="row-fluid">
                    <article class="conteudo-loop">
                        <h2 class="h2-loop-tit">
                            <a href="<?php echo real_site_url($biblio_plugin_slug); ?>resource/<?php echo $resource->django_id; ?>"><?php echo $resource->reference_title[0]; ?></a>
                            <?php foreach ( $resource->reference_title as $index => $title ): ?>
                                <?php if ( $index != 0 ): ?>
                                    <div class="altLang"><?php echo $title; ?></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </h2>

                        <?php if ( $resource->author ): ?>
                            <div class="row-fluid">
                                <?php foreach ( $resource->author as $index => $author ):
                                    echo "<a href='" . real_site_url($biblio_plugin_slug) . "?filter=author:\"" . $author . "\"'>" . $author . "</a>";
                                    echo count($resource->author)-1 != $index ? '; ' : '.';
                                endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $resource->journal ): ?>
                            <div class="row-fluid">
                                <?php
                                    echo "<a href='" . real_site_url($biblio_plugin_slug) . "?filter=journal:\"" . $resource->journal[0] . "\"'>" . $resource->journal[0] . "</a>";
                                    if ( $resource->reference_source ):
                                        echo substr($resource->reference_source[0], strpos($resource->reference_source[0], ';'), 100);
                                    endif;
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="row-fluid">
                            <?php
                                if ( $resource->publication_type ):
                                    echo ucfirst( $resource->publication_type[0] );
                                    if ( $resource->publication_language ){
                                        echo __(' in ') . strtoupper(implode(', ', $resource->publication_language));
                                    }
                                    echo ' | ';
                                endif;
                                if ( $resource->database ) echo $resource->database[0] . ' | ';
                                if ( $resource->django_id ) echo 'ID: ' . $resource->django_id;
                            ?>
                            <br/>
                        </div>

                        <?php if ( $resource->reference_abstract ): ?>
                            <div class="row-fluid abstract">
                                <strong><?php _e('Abstract','biblio'); ?></strong>
                                <?php foreach ( $resource->reference_abstract as $index => $abs ): ?>
                                    <?php $class = $index != 0 ? 'altLang' : ''; ?>
                                    <div class="abstract-version <?php echo $class; ?>"><?php echo $abs; ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($resource->descriptor ) : ?>
                            <div class="row-fluid subjects">
                                <strong><i class="fa fa-tags" aria-hidden="true"></i></strong>
                                <?php
                                    $subjects = array();
                                    foreach ( $resource->descriptor as $index => $subject ):
                                        echo "<a href='" . real_site_url($biblio_plugin_slug) . "?filter=descriptor:\"" . $subject . "\"'>" . $subject . "</a>";
                                        echo $index != count($resource->descriptor)-1 ? ', ' : '';
                                    endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $resource->link ) : ?>
                            <div class="row-fluid">
                                <?php foreach ($resource->link as $link): ?>
                                    <span class="more">
                                        <a href="<?php echo $link ?>" target="_blank">
                                            <i class="fa fa-file" aria-hidden="true"></i> <?php _e('Fulltext','biblio'); ?>
                                        </a>
                                    </span>&nbsp;&nbsp;&nbsp;
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </article>
                </div>
            </section>
            <aside id="sidebar">
                <?php if ( $resource->descriptor ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Main subject','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $resource->descriptor as $subject ): ?>
                                <li class="cat-item">
                                    <?php echo $subject; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if ( $resource->publication_type ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Publication type','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $resource->publication_type as $type ): ?>
                                <li class="cat-item">
                                    <?php echo ucfirst($type); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if ( $resource->database ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Database','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $resource->database as $db ): ?>
                                <li class="cat-item">
                                    <?php echo $db; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if ( $resource->publication_country ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Publication country','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $resource->publication_country as $cp ): ?>
                                <li class="cat-item">
                                    <?php echo $cp; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if ( $resource->check_tags ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Limits','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $resource->check_tags as $limit ): ?>
                                <li class="cat-item">
                                    <?php echo $limit; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if ( $resource->publication_language ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Language','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $resource->publication_language as $lang ): ?>
                                <li class="cat-item">
                                    <?php echo strtoupper($lang); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if ( $resource->journal ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Journal','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $resource->journal as $journal ): ?>
                                <li class="cat-item">
                                    <?php echo $journal; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>
                <?php if ( $resource->publication_year ): ?>
                    <section class="row-fluid widget_categories">
                        <header class="row-fluid">
                            <h1 class="h1-header"><?php _e('Year','biblio'); ?></h1>
                        </header>
                        <ul>
                            <li class="cat-item">
                                <?php echo $resource->publication_year; ?>
                            </li>
                        </ul>
                    </section>
                <?php endif; ?>
            </aside>
            <div class="spacer"></div>
        </div>
    </div>
<?php get_footer(); ?>
