<?php
    include "../../../../wp-load.php";

    $lang = $_GET['lang'];
    $biblio_id = $_GET['biblio_id'];

    $biblio_id_parts = explode(".", $biblio_id);
    $biblio_ref_id = $biblio_id_parts[2];

    $biggrec_api_url = "https://biggrec-api.teste.bvsalud.org/get_epistemoniko_id/" . $biblio_ref_id;
    $epistemonikos_guidelines_api_url = "https://api.iloveevidence.com/v2.1/guidelines/";

    // get similar docs
    $biggrec_json_result = @file_get_contents($biggrec_api_url);
    $biggrec_api_result = json_decode($biggrec_json_result, true);


    if ($biggrec_api_result["epistemoniko_id"] != ""){
        $epistemonikos_id = $biggrec_api_result["epistemoniko_id"];
        $epistemonikos_api_url = $epistemonikos_guidelines_api_url . $epistemonikos_id;

        $epistemonikos_json_result = @file_get_contents($epistemonikos_api_url);
        $epistemonikos_api_result = json_decode($epistemonikos_json_result, true);

        $recommendation_list = $epistemonikos_api_result["guideline"]["recommendations"];

        echo '<div class="rec-title">Recommendations included in this guideline: (' . count($recommendation_list) . ')';

        foreach ($recommendation_list as $rec){
            $detail_link = 'https://bigg-rec.bvsalud.org/recommendations/' . $rec['id'];
            echo '<div class="rec-box">';
            echo '<p>' . $rec['abstract_en'] . '</p>';
            echo '<p><span class="label">Year</span><span>' . $rec['year'] . '</span></p>';
            echo '<p><span class="more"><a href="' . $detail_link . '" target="biggrec">More details</a></span></p>';
            echo '</div>';
        }

    }


?>