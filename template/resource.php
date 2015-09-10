<?php
/*
Template Name: LILDBI-WEB Detail
*/

global $lildbi_service_url, $lildbi_plugin_slug;

$lildbi_config         = get_option('lildbi_config');
$lildbi_initial_filter = $lildbi_config['initial_filter'];
$lildbi_addthis_id     = $lildbi_config['addthis_profile_id'];
$lildbi_about          = $lildbi_config['about'];
$lildbi_tutorials      = $lildbi_config['tutorials'];

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
if ($lildbi_initial_filter != ''){
    if ($user_filter != ''){
        $filter = $lildbi_initial_filter . ' AND ' . $user_filter;
    }else{
        $filter = $lildbi_initial_filter;
    }
}else{
    $filter = $user_filter;
}

$request_uri   = $_SERVER["REQUEST_URI"];
$request_parts = explode('/', $request_uri);
$resource_id   = end($request_parts);

$site_language = strtolower(get_bloginfo('language'));
$lang_dir = substr($site_language,0,2);

$lildbi_service_request = $lildbi_service_url . 'api/bibliographic/search/?id=biblioref.referenceanalytic.' . $resource_id . '&op=related&lang=' . $lang_dir;

//print $lildbi_service_request;

$response = @file_get_contents($lildbi_service_request);

if ($response){
    $response_json = json_decode($response);
    //echo "<pre>"; print_r($response_json); echo "</pre>";
    $resource = $response_json->diaServerResponse[0]->match->docs[0];
    //$related_list = $response_json->diaServerResponse[0]->response->docs;
}

$feed_url = real_site_url($lildbi_plugin_slug) . 'lildbi-feed?q=' . urlencode($query) . '&filter=' . urlencode($filter);

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
                    <input type="hidden" name="sort" id="sort" value="">
                    <input type="hidden" name="format" id="format" value="summary">
                    <input type="hidden" name="count" id="count" value="10">
                    <input type="hidden" name="page" id="page" value="1">
                    <input value="" name="q" class="input-search" id="s" type="text" placeholder="<?php _e('Search Documents', 'lildbi'); ?>">
                    <input id="searchsubmit" value="<?php _e('Search', 'lildbi'); ?>" type="submit">
                    <a href="#" title="<?php _e('Tip! You can do your search using boolean operators.', 'lildbi'); ?>" class="help ketchup tooltip"><i class="fa fa-question-circle fa-2x"></i></a>
                </form>
            </section>
            <section id="conteudo">
                <header class="row-fluid border-bottom">
                    <h1 class="h1-header">
                        <span class="breadcrumb-home"><a href="<?php echo real_site_url($lildbi_plugin_slug); ?>"><?php _e('HOME','lildbi'); ?></a><?php echo $ref ? $ref : ''; ?></span> / <?php echo ( strlen($resource->reference_title[0]) > 30 ) ? substr($resource->reference_title[0],0,30) . '...' : $resource->reference_title[0]; ?>
                    </h1>
                    <div class="spacer"></div>
                    <div class="resultsBar">
                        <div class="formset">
                            <label for="output" class="hide"><?php _e('Presentation format', 'lildbi') ?></label>
                            <select name="output" id="output" class="inputText" onchange="javascript:change_format(this);">
                                <option value=""><?php _e('Presentation format', 'lildbi') ?></option>
                                <option value="summary"><?php _e('Short', 'lildbi') ?></option>
                                <option value="abstract"><?php _e('Detailed', 'lildbi') ?></option>
                            </select>
                        </div>
                        <div class="formset">
                            <label for="order" class="hide"><?php _e('Order by', 'lildbi') ?></label>
                            <select name="order" id="order" class="inputText" onchange="javascript:change_sort(this);">
                                <option value=""><?php _e('Order by', 'lildbi') ?></option>
                                <option value="RELEVANCE"><?php _e('Relevance', 'lildbi') ?></option>
                                <option value="YEAR_DESC"><?php _e('Descending year', 'lildbi') ?></option>
                                <option value="YEAR_ASC"><?php _e('Ascending year', 'lildbi') ?></option>
                            </select>
                        </div>
                        <div class="formset">
                            <label for="per_page" class="hide"><?php _e('Documents per page', 'lildbi') ?></label>
                            <select name="per_page" id="per_page" onchange="change_count(this);">
                                <option value=""><?php _e('Documents per page', 'lildbi') ?></option>
                                <option value="10" selected="selected">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
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
                        <script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
                        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $lildbi_addthis_id; ?>"></script>
                        <!-- AddThis Button END -->
                    </div>
                </header>
                <div class="row-fluid">
                    <ol class="doc-loop">
                        <li>
                            <h2 class="h2-loop-tit">
                                <a href="<?php echo real_site_url($lildbi_plugin_slug); ?>resource/<?php echo $resource->django_id; ?>"><?php echo $resource->reference_title[0]; ?></a>
                                <?php foreach ( $resource->reference_title as $index => $title ): ?>
                                    <?php if ( $index != 0 ): ?>
                                        <span class="altLang"><?php echo $title; ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </h2>

                            <?php if ( $resource->author ): ?>
                                <p class="row-fluid">
                                    <?php foreach ( $resource->author as $index => $author ):
                                        echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=author:\"" . $author . "\"'>" . $author . "</a>";
                                        echo count($resource->author)-1 != $index ? '; ' : '.';
                                    endforeach; ?>
                                </p>
                            <?php endif; ?>

                            <?php if ( $resource->journal ): ?>
                                <p class="row-fluid">
                                    <?php
                                        echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=journal:\"" . $resource->journal[0] . "\"'>" . $resource->journal[0] . "</a>";
                                        if ( $resource->reference_source ):
                                            echo substr($resource->reference_source, strpos($resource->reference_source, ';'), 100);
                                        endif;
                                    ?>
                                </p>
                            <?php endif; ?>

                            <p class="row-fluid">
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
                            </p>

                            <?php if ( $resource->link ) : ?>
                                <p class="row-fluid">
                                    <a href="<?php echo $resource->link[0]; ?>">
                                        <em class="fa fa-file-text-o"></em> <?php _e('Full Text','lildbi'); ?>
                                    </a>
                                </p>
                            <?php endif; ?>

                            <?php if ( $resource->reference_abstract ): ?>
                                <p class="row-fluid abstract">
                                    <strong><?php _e('ABSTRACT','lildbi'); ?></strong>
                                    <?php foreach ( $resource->reference_abstract as $index => $abs ): ?>
                                        <?php $class = $index != 0 ? 'altLang' : ''; ?>
                                        <div class="abstract-version <?php echo $class; ?>"><?php echo $abs; ?></div>
                                    <?php endforeach; ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($resource->descriptor ) : ?>
                                <p class="row-fluid subjects">
                                    <strong><?php _e('SUBJECTS','lildbi'); ?></strong>
                                    <?php
                                        $subjects = array();
                                        foreach ( $resource->descriptor as $index => $subject ):
                                            echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=descriptor:\"" . $subject . "\"'>" . $subject . "</a>";
                                            echo $index != count($resource->descriptor)-1 ? ', ' : '';
                                        endforeach; ?>
                                </p>
                            <?php endif; ?>
                        </li>
                    </ol>
                </div>
            </section>
            <aside id="sidebar">
                <?php if ( $resource->descriptor ): ?>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Main Subject','lildbi'); ?></h1>
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
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Publication Type','lildbi'); ?></h1>
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
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Database','lildbi'); ?></h1>
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
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Database','lildbi'); ?></h1>
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
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Limits','lildbi'); ?></h1>
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
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Language','lildbi'); ?></h1>
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
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Journal','lildbi'); ?></h1>
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
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Year','lildbi'); ?></h1>
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
