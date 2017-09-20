<?php
/*
Template Name: Bibliographic Detail
*/

global $biblio_service_url, $biblio_plugin_slug, $biblio_plugin_title, $similar_docs_url;

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

//print $biblio_service_request;

$response = @file_get_contents($biblio_service_request);

if ($response){
    $response_json = json_decode($response);
    // echo "<pre>"; print_r($response_json); echo "</pre>";
    $resource = $response_json->diaServerResponse[0]->match->docs[0];
    $related_docs = $response_json->diaServerResponse[0]->response->docs;

    // find similar documents
    $similar_docs_url = 'http://basalto01.bireme.br:8180/SDService/SDService';
    $temp_id = time() . $resource->django_id;
    $create_temp_profile_url = $similar_docs_url . '?psId=wpbiblioplugin&addProfile=' . $temp_id . '&sentence=' .  urlencode($resource->reference_title[0]);
    $get_similar_docs_url = $similar_docs_url . '?psId=wpbiblioplugin&getSimDocs=' . $temp_id;
    $delete_temp_profile_url = $similar_docs_url . '?psId=wpbiblioplugin&deleteProfile=' . $temp_id;

    // create temp profile
     @file_get_contents($create_temp_profile_url);
    // get similar docs
    $similar_docs_xml = @file_get_contents($get_similar_docs_url);
    // transform to php array
    $xml = simplexml_load_string($similar_docs_xml,'SimpleXMLElement',LIBXML_NOCDATA);
    $json = json_encode($xml);
    $similar_docs = json_decode($json, TRUE);
    // delete temp profile
    @file_get_contents($delete_temp_profile_url);

}

$feed_url = real_site_url($biblio_plugin_slug) . 'biblio-feed?q=' . urlencode($query) . '&filter=' . urlencode($filter);

?>

<?php get_header('biblio');?>
    <div class="row-fluid breadcrumb">
        <a href="<?php echo real_site_url(); ?>"><?php _e('Home','biblio'); ?></a> >
        <a href="<?php echo real_site_url($biblio_plugin_slug); ?>"><?php echo $biblio_plugin_title ?> </a> >
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
                            <div class="row-fluid authors">
                                <?php foreach ( $resource->author as $index => $author ):
                                    echo "<a href='" . real_site_url($biblio_plugin_slug) . "?filter=author:\"" . $author . "\"'>" . $author . "</a>";
                                    echo count($resource->author)-1 != $index ? '; ' : '.';
                                endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $resource->reference_source ): ?>
                            <div class="row-fluid">
                                <?php
                                    echo $resource->reference_source;
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="row-fluid">
                            <?php
                                echo _('Publication year') . ': ' . $resource->publication_year;
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
                                <?php if (count($resource->link) > 1): ?>
                                    <?php foreach ($resource->link as $index => $link): ?>
                                        <span class="more">
                                            <a href="<?php echo $link ?>" target="_blank">
                                                <i class="fa fa-file" aria-hidden="true"> </i>
                                                <?php ( ($index == 0) ? _e('Fulltext (primary link)','biblio') : _e('Fulltext (alternative link)','biblio')); ?>
                                            </a>
                                        </span>&nbsp;&nbsp;&nbsp;
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="more">
                                        <a href="<?php echo $resource->link[0] ?>" target="_blank">
                                            <i class="fa fa-file" aria-hidden="true"> </i> <?php _e('Fulltext','biblio'); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </article>
                </div>
            </section>
            <aside id="sidebar">
                <?php if ( count($similar_docs['document']) > 0 ): ?>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Related','biblio'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $similar_docs['document'] as $similar) { ?>
                                <li class="cat-item">

                                    <a href="http://pesquisa.bvsalud.org/portal/resource/<?php echo $lang_dir . '/' . $similar['id']; ?>" target="_blank">
                                        <?php
                                            if ( $similar['ti_' . $lang_dir] ){
                                                echo $similar['ti_' . $lang_dir];
                                            }else{
                                                echo $similar['ti_en'];
                                            }
                                        ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </section>
                <?php endif; ?>
            </aside>
            <div class="spacer"></div>
        </div>
    </div>
<?php get_footer(); ?>
