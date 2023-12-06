<?php

namespace Rules;

class RuleEngine
{
    public function rules_for_taxes($country_iso)
    {
        $taxes = json_decode(file_get_contents(WP_PLUGIN_DIR . "/springgds/vendor/itembase/postnl/countries_tax_list.json"), true);
        return $taxes[$country_iso];
    }
}
