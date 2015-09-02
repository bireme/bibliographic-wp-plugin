<?php
/*
Template Name: LILDBI-WEB Home
*/

require_once(LILDBI_PLUGIN_PATH . '/lib/Paginator.php');

global $lildbi_service_url, $lildbi_plugin_slug;

$lildbi_config = get_option('lildbi_config');
$lildbi_initial_filter = $lildbi_config['initial_filter'];
$lildbi_addthis_id = $lildbi_config['addthis_profile_id'];

$site_language = strtolower(get_bloginfo('language'));
$lang_dir = substr($site_language,0,2);

// set query using default param q (query) or s (wordpress search) or newexpr (metaiah)
$query = $_GET['s'] . $_GET['q'];
$query = stripslashes( trim($query) );

$user_filter = stripslashes($_GET['filter']);
$page = ( isset($_GET['page']) ? $_GET['page'] : 1 );
$format = ( isset($_GET['format']) ? $_GET['format'] : '' );
$sort = ( isset($_GET['sort']) ? $_GET['sort'] : '' );
$count = ( isset($_GET['count']) ? $_GET['count'] : 10 );
$index = 1;
$total = 0;
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

$lildbi_service_request = $lildbi_service_url . 'api/bibliographic/?q=' . urlencode($query) . '&fq=' . urlencode($filter) . '&start=' . $start . '&lang=' . $lang_dir;

//print $lildbi_service_request;

$response = @file_get_contents($lildbi_service_request);
if ($response){
    $response_json = json_decode($response);
    //echo "<pre>"; print_r($response_json); echo "</pre>";
    $total = $response_json->meta->total_count;
    $start = $response_json->diaServerResponse[0]->response->start;
    $docs_list = $response_json->objects;
    $descriptor_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->descriptor_filter;
    $type_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->type_filter;
    $database_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->database_filter;
    $cp_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->cp_filter;
    $limit_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->limit_filter;
    $language_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->language_filter;
    $journal_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->journal_filter;
    $year_list = $response_json->diaServerResponse[0]->facet_counts->facet_fields->$year_filter;
}

$page_url_params = real_site_url($lildbi_plugin_slug) . '?q=' . urlencode($query) . '&filter=' . urlencode($filter);
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
                            <div class="count-docs pull-right"><?php echo $total; ?> <?php echo _n( ' document', ' documents', $total, 'lildbi' ); ?></div>
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
                            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $lildbi_addthis_id; ?>"></script>
                            <!-- AddThis Button END -->
                        </div>
                    </header>
                    <div class="row-fluid">
                        <ol class="doc-loop">
                            <?php foreach ( $docs_list as $docs) { ?>
                                <li>
                                    <h2 class="h2-loop-tit">
                                        <div class="position"><?php echo $index + $start; ?>. </div>
                                        <a href="<?php echo real_site_url($lildbi_plugin_slug); ?>resource/<?php echo $docs->id; ?>"><?php echo $docs->reference_title; ?></a>
                                        <span class="altLang"><?php echo $docs->reference_title; ?></span>
                                    </h2>

                                    <?php if ( $docs->authors ): $loop = 0; ?>
                                        <p class="row-fluid">
                                            <?php foreach ( $docs->authors as $author ):
                                                $loop++;
                                                echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=author:\"" . $author . "\"'>" . $author . "</a>";
                                                echo count($docs->authors) != $loop ? ', ' : '';
                                            endforeach; ?>
                                        </p>
                                    <?php endif; ?>

                                    <p class="row-fluid">
                                        <?php echo $docs->database . ' | ID: ' . $docs->id; ?><br/>
                                    </p>

                                    <?php if ( $docs->electronic_address ) : ?>
                                        <p class="row-fluid">
                                            <a href="<?php echo $docs->electronic_address[0]->_u; ?>">
                                                <em class="fa fa-file-text-o"></em> <?php _e('Full Text','lildbi'); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ( !empty( $format ) && $format == 'abstract' ): ?>
                                        <?php if ( $docs->abstract ): $loop = 0; ?>
                                            <p class="row-fluid abstract">
                                                <strong><?php _e('ABSTRACT','lildbi'); ?></strong>
                                                <?php foreach ( $docs->abstract as $abs ): ?>
                                                    <?php $class = $loop != 0 ? 'altLang' : ''; ?>
                                                    <div class="abstract-version <?php echo $class; ?>">
                            							<?php echo ( strlen($abs->text) > 400 ? substr($abs->text,0,400) . '...' : $abs->text); ?>
                                                    </div>
                                                <?php $loop++; endforeach; ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if ($docs->author_keyword ) : ?>
                                            <p class="row-fluid subjects">
                                                <strong><?php _e('SUBJECTS','lildbi'); ?></strong>
                                                <?php
                                                    $subjects = array();
                                                    foreach ( $docs->author_keyword as $keyword => $value ):
                                                        if ( $value->_i == $lang_dir ) $subjects[] = $value->text;
                                                    endforeach;
                                                    $loop = 1;
                                                    foreach ( $subjects as $sub ):
                                                        echo "<a href='" . real_site_url($lildbi_plugin_slug) . "?filter=descriptor:\"" . $sub . "\"'>" . $sub . "</a>";
                                                        echo $loop != count($subjects) ? ', ' : ''; $loop++;
                                                    endforeach; ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                            <?php } ?>
                            <li>
                                <h2 class="h2-loop-tit"><div class="position">1. </div>Comisión Territorial de Grados Científicos: su contribución al desarrollo doctoral en la provincia Villa Clara <br/><span class="altLang">The territorial commission of scientific degree: its contribution to the development of Ph. Ds. in Villa Clara Province</span></h2>

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
                    <div class="row-fluid">
                        <?php echo $pages->display_pages(); ?>
                    </div>
                <?php endif; ?>
            </section>
            <aside id="sidebar">

                <?php dynamic_sidebar('lildbi-home');?>

                <?php if (strval($total) > 0) :?>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Main Subject','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $descriptor_list as $descriptor ) { ?>
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
                            <li class="cat-item">
                                <a href='?filter=descriptor:"Saúde Pública"'>Saúde Pública</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href='?filter=descriptor:"Promoção da Saúde"'>Promoção da Saúde</a>
                                <span class="cat-item-count">868</span>
                            </li>
                            <li class="cat-item">
                                <a href='?filter=descriptor:"Medicina Veterinária"'>Medicina Veterinária</a>
                                <span class="cat-item-count">800</span>
                            </li>
                            <li class="cat-item">
                                <a href='?filter=descriptor:"Educação em Saúde"'>Educação em Saúde</a>
                                <span class="cat-item-count">634</span>
                            </li>
                            <li class="cat-item">
                                <a href='?filter=descriptor:"Condições Sociais"'>Condições Sociais</a>
                                <span class="cat-item-count">601</span>
                            </li>
                            <li class="cat-item">
                                <a href='?filter=descriptor:"Pesquisa"'>Pesquisa</a>
                                <span class="cat-item-count">582</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Publication type','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $type_list as $type ) { ?>
                                <li class="cat-item">
                                    <?php
                                        $filter_link = '?';
                                        if ($query != ''){
                                            $filter_link .= 'q=' . $query . '&';
                                        }
                                        $filter_link .= 'filter=type:"' . $type[0] . '"';
                                        if ($user_filter != ''){
                                            $filter_link .= ' AND ' . $user_filter ;
                                        }
                                    ?>
                                    <a href='<?php echo $filter_link; ?>'><?php echo $type[0]; ?></a>
                                    <span class="cat-item-count"><?php echo $type[1]; ?></span>
                                </li>
                            <?php } ?>
                            <li class="cat-item">
                                <a href="#">Artigo</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Monografia</a>
                                <span class="cat-item-count">590</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Database','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $database_list as $db ) { ?>
                                <li class="cat-item">
                                    <?php
                                        $filter_link = '?';
                                        if ($query != ''){
                                            $filter_link .= 'q=' . $query . '&';
                                        }
                                        $filter_link .= 'filter=db:"' . $db[0] . '"';
                                        if ($user_filter != ''){
                                            $filter_link .= ' AND ' . $user_filter ;
                                        }
                                    ?>
                                    <a href='<?php echo $filter_link; ?>'><?php echo $db[0]; ?></a>
                                    <span class="cat-item-count"><?php echo $db[1]; ?></span>
                                </li>
                            <?php } ?>
                            <li class="cat-item">
                                <a href="#">LILACS</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">590</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Publication Country','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $cp_list as $cp ) { ?>
                                <li class="cat-item">
                                    <?php
                                        $filter_link = '?';
                                        if ($query != ''){
                                            $filter_link .= 'q=' . $query . '&';
                                        }
                                        $filter_link .= 'filter=cp:"' . $cp[0] . '"';
                                        if ($user_filter != ''){
                                            $filter_link .= ' AND ' . $user_filter ;
                                        }
                                    ?>
                                    <a href='<?php echo $filter_link; ?>'><?php echo $cp[0]; ?></a>
                                    <span class="cat-item-count"><?php echo $cp[1]; ?></span>
                                </li>
                            <?php } ?>
                            <li class="cat-item">
                                <a href="#">Brasil</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Chile</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Estados Unidos</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Argentina</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Colombia</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">México</a>
                                <span class="cat-item-count">590</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Limits','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $limit_list as $limit ) { ?>
                                <li class="cat-item">
                                    <?php
                                        $filter_link = '?';
                                        if ($query != ''){
                                            $filter_link .= 'q=' . $query . '&';
                                        }
                                        $filter_link .= 'filter=limit:"' . $limit[0] . '"';
                                        if ($user_filter != ''){
                                            $filter_link .= ' AND ' . $user_filter ;
                                        }
                                    ?>
                                    <a href='<?php echo $filter_link; ?>'><?php echo $limit[0]; ?></a>
                                    <span class="cat-item-count"><?php echo $limit[1]; ?></span>
                                </li>
                            <?php } ?>
                            <li class="cat-item">
                                <a href="#">Adulto</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Feminino</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Humanos</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Gravidez</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lactente</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Recém Nascido</a>
                                <span class="cat-item-count">590</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Language','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $language_list as $lang ) { ?>
                                <li class="cat-item">
                                    <?php
                                        $filter_link = '?';
                                        if ($query != ''){
                                            $filter_link .= 'q=' . $query . '&';
                                        }
                                        $filter_link .= 'filter=la:"' . $lang[0] . '"';
                                        if ($user_filter != ''){
                                            $filter_link .= ' AND ' . $user_filter ;
                                        }
                                    ?>
                                    <a href='<?php echo $filter_link; ?>'><?php echo $lang[0]; ?></a>
                                    <span class="cat-item-count"><?php echo $lang[1]; ?></span>
                                </li>
                            <?php } ?>
                            <li class="cat-item">
                                <a href="#">Português</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Inglês</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Espanhol</a>
                                <span class="cat-item-count">590</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Francês</a>
                                <span class="cat-item-count">590</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Journal','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $journal_list as $journal ) { ?>
                                <li class="cat-item">
                                    <?php
                                        $filter_link = '?';
                                        if ($query != ''){
                                            $filter_link .= 'q=' . $query . '&';
                                        }
                                        $filter_link .= 'filter=ta:"' . $journal[0] . '"';
                                        if ($user_filter != ''){
                                            $filter_link .= ' AND ' . $user_filter ;
                                        }
                                    ?>
                                    <a href='<?php echo $filter_link; ?>'><?php echo $journal[0]; ?></a>
                                    <span class="cat-item-count"><?php echo $journal[1]; ?></span>
                                </li>
                            <?php } ?>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">Lorem Ipsum</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                    <section class="row-fluid marginbottom25 widget_categories">
                        <header class="row-fluid border-bottom marginbottom15">
                            <h1 class="h1-header"><?php _e('Year','lildbi'); ?></h1>
                        </header>
                        <ul>
                            <?php foreach ( $year_list as $year ) { ?>
                                <li class="cat-item">
                                    <?php
                                        $filter_link = '?';
                                        if ($query != ''){
                                            $filter_link .= 'q=' . $query . '&';
                                        }
                                        $filter_link .= 'filter=year:"' . $year[0] . '"';
                                        if ($user_filter != ''){
                                            $filter_link .= ' AND ' . $user_filter ;
                                        }
                                    ?>
                                    <a href='<?php echo $filter_link; ?>'><?php echo $year[0]; ?></a>
                                    <span class="cat-item-count"><?php echo $year[1]; ?></span>
                                </li>
                            <?php } ?>
                            <li class="cat-item">
                                <a href="#">1999</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">2004</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">2009</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">2007</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">2000</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                            <li class="cat-item">
                                <a href="#">2011</a>
                                <span class="cat-item-count">3807</span>
                            </li>
                        </ul>
                        <div class="more-items"><a href="#"><?php _e('Show more','lildbi'); ?> <em class="fa fa-arrow-circle-o-down"></em></a></div>
                    </section>
                <? endif; ?>
            </aside>
            <div class="spacer"></div>
        </div>
    </div>
<?php get_footer(); ?>
