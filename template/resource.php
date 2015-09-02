<?php
/*
Template Name: LILDBI-WEB Detail
*/

global $lildbi_service_url, $lildbi_plugin_slug;

$lildbi_config = get_option('lildbi_config');
$lildbi_initial_filter = $lildbi_config['initial_filter'];
$lildbi_addthis_id = $lildbi_config['addthis_profile_id'];

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

$request_uri = $_SERVER["REQUEST_URI"];
$request_parts = explode('/', $request_uri);
$resource_id = end($request_parts);

$site_language = strtolower(get_bloginfo('language'));
$lang_dir = substr($site_language,0,2);

$lildbi_addthis_id = $lildbi_config['addthis_profile_id'];
$lildbi_service_request = $lildbi_service_url . 'api/bibliographic/' . $resource_id . '?op=related&lang=' . $lang_dir;

//print $lildbi_service_request;

$response = @file_get_contents($lildbi_service_request);

if ($response){
    $response_json = json_decode($response);
    //echo "<pre>"; print_r($response_json); echo "</pre>";
    $resource = $response_json->diaServerResponse[0]->match->docs[0];
    $related_list = $response_json->diaServerResponse[0]->response->docs;
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
                        <li><a href="#"><span><?php _e('About LILDBI-WEB', 'lildbi') ?></span></a></li>
                        <li><a href="#"><span><?php _e('Tutorials','lildbi'); ?></span></a></li>
                    </ul>
                </div>
            </nav>
            <div class="spacer"></div>
        </div>
        <div class="ajusta2">
            <section class="header-search">
                <form role="search" method="get" name="searchForm" id="searchForm" action="<?php echo real_site_url($lildbi_plugin_slug); ?>">
                    <input type="hidden" name="lang" id="lang" value="<?php echo $lang_dir; ?>">
                    <input type="hidden" name="sort" id="sort" value="<?php echo $sort; ?>">
                    <input type="hidden" name="format" id="format" value="<?php echo $format ? $format : 'summary'; ?>">
                    <input type="hidden" name="count" id="count" value="<?php echo $count; ?>">
                    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
                    <input value="" name="q" class="input-search" id="s" type="text" placeholder="<?php _e('Search Documents', 'lildbi'); ?>">
                    <input id="searchsubmit" value="<?php _e('Search', 'lildbi'); ?>" type="submit">
                    <a href="#" title="<?php _e('Tip! You can do your search using boolean operators.', 'lildbi'); ?>" class="help ketchup tooltip"><i class="fa fa-question-circle fa-2x"></i></a>
                </form>
            </section>
            <section id="conteudo">
                <header class="row-fluid border-bottom">
                    <h1 class="h1-header"><span class="breadcrumb-home"><a href="<?php echo real_site_url($lildbi_plugin_slug); ?>"><?php _e('HOME','lildbi'); ?></a><?php echo $ref ? $ref : ''; ?></span> / <?php echo ( strlen($response_json->reference_title) > 30 ? substr($response_json->reference_title,0,30) . '...' : $response_json->reference_title ); ?></h1>
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
                                <option value="RELEVANCE" <?php selected( $sort, 'RELEVANCE' ); ?>><?php _e('Relevance', 'lildbi') ?></option>
                                <option value="YEAR_DESC" <?php selected( $sort, 'YEAR_DESC' ); ?>><?php _e('Descending year', 'lildbi') ?></option>
                                <option value="YEAR_ASC" <?php selected( $sort, 'YEAR_ASC' ); ?>><?php _e('Ascending year', 'lildbi') ?></option>
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
                        <script type="text/javascript">var addthis_config = {"data_track_addressbar":true};</script>
                        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $lildbi_addthis_id; ?>"></script>
                        <!-- AddThis Button END -->
                    </div>
                </header>
                <div class="row-fluid">
                    <ol class="doc-loop">
                        <li>
                            <h2 class="h2-loop-tit">
                                <?php echo $response_json->reference_title; ?>
                                <span class="altLang"><?php echo $response_json->reference_title; ?></span>
                            </h2>

                            <?php if ( $response_json->authors ): $loop = 0; ?>
                                <p class="row-fluid">
                                    <?php foreach ( $response_json->authors as $author ):
                                        $loop++;
                                        echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=author:\"" . $author . "\"'>" . $author . "</a>";
                                        echo count($response_json->authors) != $loop ? ', ' : '';
                                    endforeach; ?>
                                </p>
                            <?php endif; ?>

                            <p class="row-fluid">
                                <?php echo $response_json->database . ' | ID: ' . $response_json->id; ?><br/>
                            </p>

                            <?php if ( $response_json->electronic_address ) : ?>
                                <p class="row-fluid">
                                    <a href="<?php echo $response_json->electronic_address[0]->_u; ?>">
                                        <em class="fa fa-file-text-o"></em> <?php _e('Full Text','lildbi'); ?>
                                    </a>
                                </p>
                            <?php endif; ?>

                            <?php if ( $response_json->abstract ): $loop = 0; ?>
                                <p class="row-fluid abstract">
                                    <strong><?php _e('ABSTRACT','lildbi'); ?></strong>
                                    <?php foreach ( $response_json->abstract as $abs ): ?>
                                        <?php $class = $loop != 0 ? 'altLang' : ''; ?>
                                        <div class="abstract-version <?php echo $class; ?>">
                							<?php echo $abs->text; ?>
                                        </div>
                                    <?php $loop++; endforeach; ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($response_json->author_keyword ) : ?>
                                <p class="row-fluid subjects">
                                    <strong><?php _e('SUBJECTS','lildbi'); ?></strong>
                                    <?php
                                        foreach ( $response_json->author_keyword as $keyword => $value ):
                                            if ( $value->_i == $lang_dir ) $subjects[] = $value->text;
                                        endforeach;
                                        $loop = 1;
                                        foreach ( $subjects as $sub ):
                                            echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=descriptor:\"" . $sub . "\"'>" . $sub . "</a>";
                                            echo $loop != count($subjects) ? ', ' : ''; $loop++;
                                        endforeach;
                                    ?>
                                </p>
                            <?php endif; ?>
                        </li>

                        <li>
                            <h2 class="h2-loop-tit">Comisión Territorial de Grados Científicos: su contribución al desarrollo doctoral en la provincia Villa Clara <br/><span class="altLang">The territorial commission of scientific degree: its contribution to the development of Ph. Ds. in Villa Clara Province</span></h2>

                            <p class="row-fluid">
                                <a href="#">Ruiz Hernández, José Ramón</a>
                            </p>
                            <p class="row-fluid">
                                <a href="#">Edumecentro</a>; 7(3): 1-19, jul.-set. 2015. ilus.
                            </p>
                            <p class="row-fluid">
                                Artigo em Espanhol | LILACS-Express | ID: lil-749569
                            </p>
                            <p class="row-fluid">
                                Número de Classificação: 614.32 | Autor: T17a | Volume 1: v.1 | Tombo: 1001
                            </p>
                            <p class="row-fluid">
                                <a href="#"><em class="fa fa-file-text-o"></em> Texto Completo</a>
                            </p>
                        </li>
                    </ol>
                </div>
            </section>
            <aside id="sidebar">
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Main Subject','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <?php if ($response_json->author_keyword ) : ?>
                            <?php foreach ( $response_json->author_keyword as $keyword => $value ): ?>
                                <?php if ( $value->_i == $lang_dir ): ?>
                                    <li class="cat-item">
                                        <?php echo $value->text; ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </section>
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Publication type','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <li class="cat-item">
                            Artigo
                        </li>
                    </ul>
                </section>
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Database','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <li class="cat-item">
                            LILACS
                        </li>
                    </ul>
                </section>
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Publication Country','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <li class="cat-item">
                            Cuba
                        </li>
                    </ul>
                </section>
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Limits','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <li class="cat-item">
                            Adulto
                        </li>
                        <li class="cat-item">
                            Feminino
                        </li>
                        <li class="cat-item">
                            Humanos
                        </li>
                        <li class="cat-item">
                            Gravidez
                        </li>
                        <li class="cat-item">
                            Lactente
                        </li>
                        <li class="cat-item">
                            Recém Nascido
                        </li>
                    </ul>
                </section>
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Language','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <li class="cat-item">
                            Espanhol
                        </li>
                    </ul>
                </section>
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Journal','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <li class="cat-item">
                            Lorem Ipsum
                        </li>
                    </ul>
                </section>
                <section class="row-fluid marginbottom25 widget_categories">
                    <header class="row-fluid border-bottom marginbottom15">
                        <h1 class="h1-header"><?php _e('Year','lildbi'); ?></h1>
                    </header>
                    <ul>
                        <li class="cat-item">
                            2004
                        </li>
                    </ul>
                </section>
            </aside>
            <div class="spacer"></div>
        </div>
    </div>
<?php get_footer(); ?>
