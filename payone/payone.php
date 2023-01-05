<?php

defined('MOODLE_INTERNAL') || die();

class Payone {
    private $portalKey;
    private $frontendUrl;
    private $items = 1;
    private $data = array();
    private $hash_params = array(
        "access_aboperiod",
        "access_aboprice",
        "access_canceltime",
        "access_expiretime",
        "access_period",
        "access_price",
        "access_starttime",
        "access_vat",
        "accesscode",
        "accessname",
        "addresschecktype",
        "aid",
        "amount",
        "amount_recurring",
        "amount_trail",
        "api_version",
        "backurl",
        "booking_date",
        "checktype",
        "clearingtype",
        "consumerscoretype",
        "currency",
        "customerid",
        "de",
        "de_recurring",
        "de_trail",
        "document_date",
        "due_time",
        "eci",
        "ecommercemode",
        "encoding",
        "errorurl",
        "exiturl",
        "getusertoken",
        "id",
        "id_recurring",
        "id_trail",
        "invoice_deliverydate",
        "invoice_deliveryenddate",
        "invoice_deliverymode",
        "invoiceappendix",
        "invoiceid",
        "it",
        "mandate_identification",
        "mid",
        "mode",
        "narrative_text",
        "no",
        "no_recurring",
        "no_trail",
        "param",
        "period_length_recurring",
        "period_length_trail",
        "period_unit_recurring",
        "period_unit_trail",
        "portalid",
        "pr",
        "pr_recurring",
        "pr_trail",
        "productid",
        "reference",
        "request",
        "responsetype",
        "settleaccount",
        "settleperiod",
        "settletime",
        "storecarddata",
        "successurl",
        "targetwindow",
        "ti",
        "ti_recurring",
        "ti_trail",
        "userid",
        "va",
        "va_recurring",
        "va_trail",
        "vaccountname",
        "vreference"
    );

    /**
     * payone constructor.
     */
    public function __construct() {
        global $USER;

        $this->frontendUrl = get_config('enrol_ildpayone', 'frontend_url');
        $this->portalKey = get_config('enrol_ildpayone', 'portalkey');
        $this->data['portalid'] = get_config('enrol_ildpayone', 'portalid');
        $this->data['mid'] = get_config('enrol_ildpayone', 'mid');
        $this->data['aid'] = get_config('enrol_ildpayone', 'aid');
        $this->data['api_version'] = get_config('enrol_ildpayone', 'api_version');
        $this->data['mode'] = get_config('enrol_ildpayone', 'mode');
        $testusers = explode(',', get_config('enrol_ildpayone', 'testuser'));
        foreach($testusers as $testuser){
            if($testuser == $USER->id){
                $this->data['mode'] = 'test';
                break;
            }
        }
        $this->data['request'] = 'authorization';
        $this->data['encoding'] = 'UTF-8';
        $this->data['amount'] = 0;
    }

    /**
     * Add Item to purchase.
     *
     * @param $itemnr
     * @param $price
     * @param $quantity
     * @param $desc
     * @param $tax
     */
    public function addItem($itemtype, $itemnr, $price, $quantity, $desc, $tax) {
        $price = round(($price * 100.00), 0);
        $counter = $this->items;

        $this->data['it'][$counter] = $itemtype;
        $this->data['id'][$counter] = $itemnr;
        $this->data['pr'][$counter] = $price;
        $this->data['no'][$counter] = $quantity;
        $this->data['de'][$counter] = $desc;
        $this->data['va'][$counter] = $tax;

        $this->data['amount'] += $price * $quantity;
        $this->items++;
    }

    /**
     * Add additional parameter.
     *
     * @param mixed $parameter
     */
    public function addParameter($parameter) {
        $this->data = array_merge($this->data, $parameter);
    }

    /**
     * Add Customer.
     *
     * @param mixed $customer
     */
    public function addCustomer($customer) {
        $this->data = array_merge($this->data, $customer);
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Generate Payone IFrame-Link
     *
     * @param $clearing_type
     * @return string
     */
    public function generateLink($clearing_type) {
        $urls_params = array();
        $protected_params = array();
        $this->data['clearingtype'] = $clearing_type;

        foreach ($this->data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $i => $i_value) {
                    if ($i_value != '') {
                        $i_key = $key . '[' . $i . ']';
                        $urls_params[$i_key] = $i_value;
                        if (in_array($key, $this->hash_params)) {
                            $protected_params[$i_key] = $i_value;
                        }
                    }
                }
            } else {
                if (in_array($key, $this->hash_params)) {
                    $protected_params[$key] = $value;
                }
            }
        }

        ksort($protected_params);

        $hash_string = '';
        foreach ($protected_params as $key => $value) {
            $hash_string .= $value;
        }

        $hash = hash_hmac("sha384", $hash_string, $this->portalKey);

        $url = $this->frontendUrl;
        $url .= http_build_query($this->data);
        $url .= '&hash=' . $hash;

        return utf8_encode($url);
    }
}