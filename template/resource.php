<?php
/*
Template Name: Bibliographic Detail
*/

global $biblio_service_url, $biblio_plugin_slug, $similar_docs_url;

$biblio_config         = get_option('biblio_config');
$biblio_initial_filter = $biblio_config['initial_filter'];
$biblio_addthis_id     = $biblio_config['addthis_profile_id'];
$biblio_about          = $biblio_config['about'];
$biblio_tutorials      = $biblio_config['tutorials'];
$alternative_links     = (bool)$biblio_config['alternative_links'];

$referer = wp_get_referer();
$path = parse_url($referer);
if ( array_key_exists( 'query', $path ) ) {
    $path = parse_str($path['query'], $output);
    // echo "<pre>"; print_r($output); echo "</pre>";
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
$resource_id   = sanitize_text_field($_GET['id']);

$site_language = strtolower(get_bloginfo('language'));
$lang = substr($site_language,0,2);

$biblio_service_request = $biblio_service_url . 'api/bibliographic/search/?id=' . $resource_id . '&op=related&lang=' . $lang;

//print($biblio_service_request);

$response = @file_get_contents($biblio_service_request);

if ($response){
    $response_json = json_decode($response);
    $resource = $response_json->diaServerResponse[0]->match->docs[0];
    // create param to find similars
    $similar_text = $resource->reference_title[0];
    if (isset($resource->mj)){
        $similar_text .= ' ' . implode(' ', $resource->mj);
    }

    $similar_docs_url = $similar_docs_url . '?adhocSimilarDocs=' . urlencode($similar_text);
    $similar_docs_request = ( $biblio_config['default_filter_db'] ) ? $similar_docs_url . '&sources=' . $biblio_config['default_filter_db'] : $similar_docs_url;
    $similar_query = urlencode($similar_docs_request);
    $related_query = urlencode($similar_docs_url);

    // create param to find publication language
    if (isset($resource->publication_language[0])){
        $publication_language = explode('|', $resource->publication_language[0]);
        $publication_language = get_publication_language($publication_language, $lang);
    }
}

$feed_url = real_site_url($biblio_plugin_slug) . 'biblio-feed?q=' . urlencode($query) . '&filter=' . urlencode($filter);

$home_url = isset($biblio_config['home_url_' . $lang]) ? $biblio_config['home_url_' . $lang] : real_site_url();
$plugin_breadcrumb = isset($biblio_config['plugin_title_' . $lang]) ? $biblio_config['plugin_title_' . $lang] : $biblio_config['plugin_title'];

// related resources types
$type_translated['cin'] = __('Comment in','biblio');
$type_translated['con'] = __('Comment on','biblio');
$type_translated['ein'] = __('Erratum in','biblio');
$type_translated['efr'] = __('Erratum for','biblio');
$type_translated['cri'] = __('Corrected and republished in','biblio');
$type_translated['crf'] = __('Corrected and republished from','biblio');
$type_translated['uin'] = __('Update in','biblio');
$type_translated['uof'] = __('Update of','biblio');
$type_translated['eci'] = __('Expression of concern in','biblio');
$type_translated['ecf'] = __('Expression of concern for','biblio');
$type_translated['rpi'] = __('Republished in','biblio');
$type_translated['rpf'] = __('Republished from','biblio');
$type_translated['spin'] = __('Summary for patients in','biblio');
$type_translated['ori'] = __('Original report in','biblio');
$type_translated['rin'] = __('Retraction in','biblio');
$type_translated['rof'] = __('Retraction of','biblio');

?>

<?php
 ob_start();
 get_header('biblio');
 $get_header_content = ob_get_contents();
 ob_end_clean();

 $meta = array();
 $meta[] = '<!-- bibliographic metadata -->';
 $meta[] = '<meta name="citation_id" content="biblio-' . $resource->django_id . '" />';
 $meta[] = '<meta name="dc.identifier" content="biblio-' . $resource->django_id . '" />';
 foreach ( $resource->reference_title as $meta_title ){
     $meta[] = '<meta name="dc.title" content="' . $meta_title . '" />';
 }
 $meta[] = '<meta name="citation_title" content="' . $resource->reference_title[0] . '" />';

 if ( $resource->author ){
     foreach ( $resource->author as $meta_author ){
         $meta[] = '<meta name="dc.contributor"  content="' . $meta_author . '" />';
         $meta[] = '<meta name="citation_author" content="' . $meta_author . '" />';
     }
 }

 if ( $resource->journal ){
     $meta[] = '<meta name="citation_journal_title" content="' . $resource->journal[0] . '" />';
 }

 if ( $resource->link ) {
     $meta[] = '<meta name="citation_fulltext_html_url" content="' . $resource->link[0] . '" />';
 }

 if ($resource->mh ) {
     foreach ( $resource->mh as $meta_keyword ){
         $meta[] = '<meta name="citation_keywords" content="' . $meta_keyword . '" />';
     }
 }

 $meta[] = '<meta name="citation_language" content="' . $publication_language . '" />';
 if ($resource->publication_year ) {
     $meta[] = '<meta name="citation_publication_date" content="' . $resource->publication_year . '" />';
     $meta[] = '<meta name="dc.date" content="' . $resource->publication_year . '" />';
 }
 $meta_lines = implode("\n", $meta);

 $header_content = str_replace('</head>', $meta_lines . '</head>', $get_header_content);
?>

<?php print($header_content); ?>

    <div id="content" class="row-fluid">
        <div class="ajusta2">
            <div class="row-fluid breadcrumb">
                <a href="<?php echo $home_url ?>"><?php _e('Home','biblio'); ?></a> >
                <a href="<?php echo real_site_url($biblio_plugin_slug); ?>"><?php echo $plugin_breadcrumb ?> </a> >
                <?php echo ( strlen($resource->reference_title[0]) > 90 ) ? substr($resource->reference_title[0],0,90) . '...' : $resource->reference_title[0]; ?>
            </div>

            <section class="header-search">
                <form role="search" method="get" name="searchForm" id="searchForm" action="<?php echo real_site_url($biblio_plugin_slug); ?>">
                    <input type="hidden" name="lang" id="lang" value="<?php echo $lang; ?>">
                    <input type="hidden" name="sort" id="sort" value="">
                    <input type="hidden" name="format" id="format" value="summary">
                    <input type="hidden" name="count" id="count" value="10">
                    <input type="hidden" name="page" id="page" value="1">
                    <input value="" name="q" class="input-search" id="s" type="text" placeholder="<?php _e('Enter one or more words', 'biblio'); ?>">
                    <input id="searchsubmit" value="<?php _e('Search', 'biblio'); ?>" type="submit">
                    <a href="#" title="<?php _e('Tip! You can do your search using boolean operators.', 'biblio'); ?>" class="help ketchup tooltip"><i class="fa fa-question-circle fa-2x"></i></a>
                </form>
            </section>
            <div class="content-area detail">
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
                        <script type="text/javascript">var addthis_config = {"data_track_addressbar":false};</script>
                        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $biblio_addthis_id; ?>"></script>
                        <!-- AddThis Button END -->
                    </div>
                    <div class="row-fluid">
                        <article class="conteudo-loop">
                            <h2 class="h2-loop-tit">
                                <a href="#"><?php echo $resource->reference_title[0]; ?></a>
                                <?php foreach ( $resource->reference_title as $index => $title ): ?>
                                    <?php if ( $index != 0 ): ?>
                                        <div class="altLang"><?php echo $title; ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if ( $resource->english_title ): ?>
                                    <div class="altLang"><?php echo $resource->english_title; ?></div>
                                <?php endif; ?>
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
                                    <?php echo $resource->reference_source; ?>
                                </div>
                            <?php endif; ?>

                            <div class="row-fluid">
                                <?php echo __('Publication year', 'biblio') . ': ' . $resource->publication_year; ?>
                            </div>

                            <?php if ( 'T' == $resource->publication_type[0] ) : ?>
                                <div class="row-fluid pub-type">
                                    <?php
                                        if ( $publication_language ) {
                                            $text = __('Theses and dissertations in %pub_lang% presented to the %institution% to obtain the academic title of %academic_title%. Leader: %leader%', 'biblio');
                                            $text = str_replace('%pub_lang%', $publication_language, $text);
                                        } else {
                                            $text = __('Theses and dissertations presented to the %institution% to obtain the academic title of %academic_title%. Leader: %leader%', 'biblio');
                                        }

                                        $text = str_replace('%institution%', $resource->thesis_dissertation_institution[0], $text);
                                        $text = str_replace('%academic_title%', $resource->thesis_dissertation_academic_title[0], $text);
                                        $text = str_replace('%leader%', $resource->thesis_dissertation_leader[0], $text);

                                        echo $text;
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if ( $resource->abstract_language ): ?>
                                <div class="row-fluid abstract" id="tabs">

                                    <ul>
                                      <?php foreach ( $resource->abstract_language as $index => $ab ):
                                              $language = explode('|', $ab);
                                              switch ($language[0]) {
                                                case 'pt':
                                                  $abstract = 'Resumo';
                                                  break;
                                                case 'en':
                                                  $abstract = 'Abstract';
                                                  break;
                                                case 'es':
                                                  $abstract = 'Resumen';
                                                  break;
                                                case 'fr':
                                                  $abstract = 'Résumé';
                                                  break;

                                                default:
                                                  $abstract = 'Abstract';
                                                  break;
                                              }
                                              $class = $index == 0 ? 'active' : ''; ?>
                                        <li class="<?php echo $class; ?>"><button  onclick="tabs(<?php echo $index; ?>);"><?php echo $abstract; ?></button></li>
                                      <?php endforeach; ?>
                                    </ul>
                                    <hr>

                                    <?php foreach ( $resource->abstract_language as $index => $ab ): ?>
                                        <?php $class = $index != 0 ? 'altLang' : ''; ?>
                                        <div class="abstract-version <?php echo $class; ?>" id="tab-<?php echo $index; ?>">
                                            <?php
                                                $ab_clean = str_replace(array("\\r\\n", "\\t", "\\r", "\\n", "pt|", "en|", "es|", "fr|"), '' , $ab);
                                                // mark abstract sections
                                                $ab_mark = preg_replace("/(\A|\.)([\w{Lu}\s]+:)/u", "$1<h2>$2</h2>", $ab_clean);
                                                echo $ab_mark;

                                            ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($resource->mh ) : ?>
                                <div class="row-fluid subjects">
                                    <strong><i class="fa fa-tags" aria-hidden="true"></i></strong>
                                    <?php
                                        $subjects = array();
                                        foreach ( $resource->mh as $index => $subject ):
                                            echo "<a href='" . real_site_url($biblio_plugin_slug) . "?q=mh:\"" . $subject . "\"'>" . $subject . "</a>";
                                            echo $index != count($resource->mh)-1 ? ', ' : '';
                                        endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ( $resource->link ) : ?>
                                <div class="row-fluid">
                                    <?php if ($alternative_links && count($resource->link) > 10): ?>
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

                    <?php if ( $resource->related_resources ) : ?>
                        <?php
                            foreach ($resource->related_resources as $related):
                                $related_data = json_decode($related, true);
                                $related_type = $related_data['_i'];
                                $related_link = ($related_data['_6'] != '' ? $related_data['_6'] : 'https://pesquisa.bvsalud.org/portal/resource/' . $lang . '/' . $related_data['_w']);
                                echo '<strong>' . $type_translated[$related_type] . ':</strong>';
                                echo '<p><a href="' . $related_link . '" target="_blank">' . $related_data['_t'] . '</a></p>';
                            endforeach;
                        ?>
                    <?php endif; ?>

                    <?php if ( $resource->related_research ) : ?>
                        <?php

                            echo '<strong>' . __('Research data','biblio') . '</strong>';
                            foreach ($resource->related_research as $research):
                                $research_data = json_decode($research, true);
                                $research_link = $research_data['_6'];
                                echo '<p>';
                                echo '<a href="' . $research_link . '" target="_blank">' . $research_data['_t'] . '</a><br/>';
                                echo $research_data['_n'];
                                echo '</p>';
                            endforeach;
                        ?>
                    <?php endif; ?>

                    <?php if ( strpos($biblio_initial_filter, 'BIGG') === false ): ?>
                        <div class="row-fluid">
                            <header class="row-fluid border-bottom marginbottom15">
                                <h1 class="h1-header"><?php _e('More related','biblio'); ?></h1>
                            </header>
                            <div id="loader" class="loader" style="display: inline-block;"></div>
                            <div id="async-alert" style="display: none;"><?php echo __('No related documents', 'biblio'); ?></div>
                        </div>
                    <?php else:  ?>
                        <div id="epistemonikos"> </div>
                    <?php endif; ?>

                    <div class="row-fluid">
                        <div id="async" class="related-docs">

                        </div>
                    </div>
<?php
$sources = ( $biblio_config['extra_filter_db'] ) ? $biblio_config['extra_filter_db'] : '';
$url = BIBLIOGRAPHIC_PLUGIN_URL.'template/related.php?query='.$related_query.'&sources='.$sources.'&lang='.$lang;
?>

<?php if ( strpos($biblio_initial_filter, 'BIGG') === false ): ?>
    <script type="text/javascript">
        show_related("<?php echo $url; ?>");
    </script>
<?php endif; ?>

                </section>
                <aside id="sidebar">
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Related','biblio'); ?></h1>
                        </header>
                        <ul id="ajax">

                        </ul>
                    </section>
<?php
$url = BIBLIOGRAPHIC_PLUGIN_URL.'template/similar.php?query='.$similar_query.'&lang='.$lang;
?>
<script type="text/javascript">
    show_similar("<?php echo $url; ?>");
</script>
                </aside>
                <div class="spacer"></div>
            </div> <!-- close DIV.detail-area -->
        </div> <!-- close DIV.detail-area -->
    </div>

<?php
if ( strpos($biblio_initial_filter, 'BIGG') !== false ):
    $epistemonikos_url = BIBLIOGRAPHIC_PLUGIN_URL.'template/epistemonikos.php?biblio_id=' . $resource_id;
?>

    <script type="text/javascript">
        show_epistemonikos("<?php echo $epistemonikos_url; ?>");
    </script>

<?php endif; ?>

<?php get_footer(); ?>
