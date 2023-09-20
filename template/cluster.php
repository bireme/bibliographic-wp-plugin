<?php

ini_set('display_errors', '0');

$lang = $_POST['lang'];
$site_lang = $_POST['site_lang'];
$query = $_POST['query'];
$filter = $_POST['filter'];
$user_filter = $_POST['uf'];
$fb = $_POST['fb'];
$cluster = $_POST['cluster'];
$cluster_fb = ( $_POST['cluster'] ) ? $_POST['cluster'].':'.$fb : '';
$count = 1;

$patterns = array(
    'A' => '(á|à|â|ä|Á|À|Â|Ä)',
    'E' => '(é|è|ê|ë|É|È|Ê|Ë)',
    'I' => '(í|ì|î|ï|Í|Ì|Î|Ï)',
    'O' => '(ó|ò|ô|ö|Ó|Ò|Ô|Ö)',
    'U' => '(ú|ù|û|ü|Ú|Ù|Û|Ü)',
    'C' => '(ç|Ç)',
    'N' => '(ñ|Ñ)'
);

$biblio_service_request = $biblio_service_url . 'api/bibliographic/search/?q=' . urlencode($query) . '&fq=' . urlencode($filter) . '&fb=' . $cluster_fb . '&lang=' . $lang . '&count=' . $count;

// echo "<pre>"; print_r($biblio_service_request); echo "</pre>"; die();

$response = @file_get_contents($biblio_service_request);
if ($response){
    $response_json = json_decode($response);
    // echo "<pre>"; print_r($response_json); echo "</pre>";
    $total = $response_json->diaServerResponse[0]->response->numFound;
    $start = $response_json->diaServerResponse[0]->response->start;
    $docs_list = $response_json->diaServerResponse[0]->response->docs;
    $facet_list = (array) $response_json->diaServerResponse[0]->facet_counts->facet_fields;

    if ( array_key_exists('publication_year', $facet_list)) {
        usort($facet_list['publication_year'], function($a, $b) {
            return $b[0] <=> $a[0];
        });
    }

    if ( array_key_exists('publication_country', $facet_list)) {
        usort($facet_list['publication_country'], function($a, $b) use ($lang, $patterns) {
            $a[0] = biblio_get_lang_value($a[0], $lang);
            $a[0] = preg_replace(array_values($patterns), array_keys($patterns), $a[0]);
            $b[0] = biblio_get_lang_value($b[0], $lang);
            $b[0] = preg_replace(array_values($patterns), array_keys($patterns), $b[0]);
            return $a[0] <=> $b[0];
        });
    }
}

?>

<?php if ( $facet_list[$cluster] ) : ?>
    <ul class="filter-list">
        <?php foreach ( $facet_list[$cluster] as $filter_item ) { ?>
            <?php
                $filter_value = $filter_item[0];
                $filter_count = $filter_item[1];

                if ($cluster == 'descriptor_filter') {
                    $cluster = 'mj';
                }
            ?>
            <?php if ( 'mj' != $cluster || filter_var($filter_value, FILTER_VALIDATE_INT) === false ) : ?>
                <li class="cat-item">
                    <?php
                        $filter_link = '?';
                        if ($query != ''){
                            $filter_link .= 'q=' . $query . '&';
                        }
                        $filter_link .= 'filter=' . $cluster . ':"' . $filter_value . '"';
                        if ($user_filter != ''){
                            $filter_link .= ' AND ' . $user_filter ;
                        }
                    ?>
                    <?php if ( strpos($filter_value, '^') !== false ): ?>
                        <a href='<?php echo $filter_link; ?>'><?php biblio_print_lang_value($filter_value, $site_lang); ?></a>
                    <?php elseif ( array_key_exists($cluster, $biblio_texts) ): ?>
                        <a href='<?php echo $filter_link; ?>'><?php  echo translate_label($biblio_texts, $filter_value, $cluster); ?></a>
                    <?php else: ?>
                        <a href='<?php echo $filter_link; ?>'><?php echo $filter_value; ?></a>
                    <?php endif; ?>
                    <span class="cat-item-count"><?php echo $filter_count; ?></span>
                </li>
            <?php endif; ?>
        <?php } ?>
    </ul>
<?php endif; ?>