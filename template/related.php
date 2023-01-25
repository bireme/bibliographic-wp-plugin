<?php
    include "../../../../wp-load.php";

    $lang = sanitize_text_field($_GET['lang']);
    $similar_docs_url = sanitize_text_field($_GET['query']);
    $filter_db = sanitize_text_field($_GET['sources']);
    $similar_docs = array();

    if ( $filter_db ) {
        $sources = explode(',', $filter_db);

        foreach ( $sources as $src ) {
            // get similar docs
            $similar_docs_xml = @file_get_contents($similar_docs_url.'&sources='.$src);
            // transform to php array
            $xml = simplexml_load_string($similar_docs_xml,'SimpleXMLElement',LIBXML_NOCDATA);
            $json = json_encode($xml);
            $similar_docs[$src] = json_decode($json, TRUE);
        }
    }

    if ( $similar_docs ) {
        foreach ( $similar_docs as $src => $docs ) {
            ?>
            <div class="box4">
                <h2 class="h2-loop-tit"><?php echo strtoupper($src); ?></h2>
                <ul>
                <?php
                    if ( array_key_exists('document', $docs) ) {
                        foreach ( $docs['document'] as $similar ) {
                            ?>
                            <li class="cat-item">
                                <a href="http://pesquisa.bvsalud.org/portal/resource/<?php echo $lang . '/' . $similar['id']; ?>" target="_blank">
                                <?php
                                    $preferred_lang_list = array($lang, 'en', 'es', 'pt');
                                    $similar_title = '';
                                    // start with more generic title
                                    if (isset($similar['ti'])){
                                        $similar_title = is_array($similar['ti']) ? $similar['ti'][0] : $similar['ti'];
                                    }
                                    // search for title in different languages
                                    foreach ($preferred_lang_list as $lang){
                                        $ti_lang = 'ti_' . $lang;
                                        if (isset($similar[$ti_lang])){
                                            $similar_title = $similar[$ti_lang];
                                            break;
                                        }
                                    }
                                    echo $similar_title;
                                ?>
                                </a>
                            </li>
                            <?php
                        }
                    } else {
                        echo '<li>' . __('No related documents', 'biblio') . '</li>';
                    }
                ?>
                </ul>
            </div>
            <?php
        }
    }
?>