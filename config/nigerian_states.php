<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nigerian States & FCT
    |--------------------------------------------------------------------------
    |
    | All 36 Nigerian States plus the Federal Capital Territory (FCT).
    | Each entry includes the state code, name, and the name of its
    | State Internal Revenue Service (SIRS) for PAYE and individual WHT routing.
    |
    */

    'states' => [
        'AB' => ['name' => 'Abia', 'sirs' => 'Abia State Internal Revenue Service'],
        'AD' => ['name' => 'Adamawa', 'sirs' => 'Adamawa State Internal Revenue Service'],
        'AK' => ['name' => 'Akwa Ibom', 'sirs' => 'Akwa Ibom State Internal Revenue Service'],
        'AN' => ['name' => 'Anambra', 'sirs' => 'Anambra State Internal Revenue Service'],
        'BA' => ['name' => 'Bauchi', 'sirs' => 'Bauchi State Internal Revenue Service'],
        'BY' => ['name' => 'Bayelsa', 'sirs' => 'Bayelsa State Internal Revenue Service'],
        'BE' => ['name' => 'Benue', 'sirs' => 'Benue State Internal Revenue Service'],
        'BO' => ['name' => 'Borno', 'sirs' => 'Borno State Internal Revenue Service'],
        'CR' => ['name' => 'Cross River', 'sirs' => 'Cross River State Internal Revenue Service'],
        'DE' => ['name' => 'Delta', 'sirs' => 'Delta State Internal Revenue Service'],
        'EB' => ['name' => 'Ebonyi', 'sirs' => 'Ebonyi State Internal Revenue Service'],
        'ED' => ['name' => 'Edo', 'sirs' => 'Edo State Internal Revenue Service'],
        'EK' => ['name' => 'Ekiti', 'sirs' => 'Ekiti State Internal Revenue Service'],
        'EN' => ['name' => 'Enugu', 'sirs' => 'Enugu State Internal Revenue Service'],
        'GO' => ['name' => 'Gombe', 'sirs' => 'Gombe State Internal Revenue Service'],
        'IM' => ['name' => 'Imo', 'sirs' => 'Imo State Internal Revenue Service'],
        'JI' => ['name' => 'Jigawa', 'sirs' => 'Jigawa State Internal Revenue Service'],
        'KD' => ['name' => 'Kaduna', 'sirs' => 'Kaduna State Internal Revenue Service'],
        'KN' => ['name' => 'Kano', 'sirs' => 'Kano State Internal Revenue Service'],
        'KT' => ['name' => 'Katsina', 'sirs' => 'Katsina State Internal Revenue Service'],
        'KE' => ['name' => 'Kebbi', 'sirs' => 'Kebbi State Internal Revenue Service'],
        'KO' => ['name' => 'Kogi', 'sirs' => 'Kogi State Internal Revenue Service'],
        'KW' => ['name' => 'Kwara', 'sirs' => 'Kwara State Internal Revenue Service'],
        'LA' => ['name' => 'Lagos', 'sirs' => 'Lagos Internal Revenue Service (LIRS)'],
        'NA' => ['name' => 'Nasarawa', 'sirs' => 'Nasarawa State Internal Revenue Service'],
        'NI' => ['name' => 'Niger', 'sirs' => 'Niger State Internal Revenue Service'],
        'OG' => ['name' => 'Ogun', 'sirs' => 'Ogun State Internal Revenue Service'],
        'ON' => ['name' => 'Ondo', 'sirs' => 'Ondo State Internal Revenue Service'],
        'OS' => ['name' => 'Osun', 'sirs' => 'Osun State Internal Revenue Service'],
        'OY' => ['name' => 'Oyo', 'sirs' => 'Oyo State Internal Revenue Service'],
        'PL' => ['name' => 'Plateau', 'sirs' => 'Plateau State Internal Revenue Service'],
        'RI' => ['name' => 'Rivers', 'sirs' => 'Rivers State Internal Revenue Service'],
        'SO' => ['name' => 'Sokoto', 'sirs' => 'Sokoto State Internal Revenue Service'],
        'TA' => ['name' => 'Taraba', 'sirs' => 'Taraba State Internal Revenue Service'],
        'YO' => ['name' => 'Yobe', 'sirs' => 'Yobe State Internal Revenue Service'],
        'ZA' => ['name' => 'Zamfara', 'sirs' => 'Zamfara State Internal Revenue Service'],
        'FC' => ['name' => 'FCT', 'sirs' => 'FCT Internal Revenue Service (FCT-IRS)'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Remita Service Type IDs per Tax Authority
    |--------------------------------------------------------------------------
    |
    | Each state IRS and the federal FIRS have their own Remita Service Type ID
    | for receiving tax payments. These must be obtained from each authority
    | or from Remita's billing configuration.
    |
    | Format: 'STATE_CODE' => 'service_type_id'
    | The 'firs' key is for all federal taxes (VAT, CIT, company WHT).
    |
    | Set these in your .env file as REMITA_SERVICE_TYPE_{STATE_CODE}
    | e.g., REMITA_SERVICE_TYPE_LA=12345 for Lagos PAYE
    |
    */

    'remita_service_types' => [
        // Federal (FIRS) — for VAT, CIT, company WHT
        'firs' => env('REMITA_SERVICE_TYPE_FIRS', env('REMITA_SERVICE_TYPE_ID', '')),

        // State-specific service type IDs for PAYE and individual WHT
        'AB' => env('REMITA_SERVICE_TYPE_AB', ''),
        'AD' => env('REMITA_SERVICE_TYPE_AD', ''),
        'AK' => env('REMITA_SERVICE_TYPE_AK', ''),
        'AN' => env('REMITA_SERVICE_TYPE_AN', ''),
        'BA' => env('REMITA_SERVICE_TYPE_BA', ''),
        'BY' => env('REMITA_SERVICE_TYPE_BY', ''),
        'BE' => env('REMITA_SERVICE_TYPE_BE', ''),
        'BO' => env('REMITA_SERVICE_TYPE_BO', ''),
        'CR' => env('REMITA_SERVICE_TYPE_CR', ''),
        'DE' => env('REMITA_SERVICE_TYPE_DE', ''),
        'EB' => env('REMITA_SERVICE_TYPE_EB', ''),
        'ED' => env('REMITA_SERVICE_TYPE_ED', ''),
        'EK' => env('REMITA_SERVICE_TYPE_EK', ''),
        'EN' => env('REMITA_SERVICE_TYPE_EN', ''),
        'GO' => env('REMITA_SERVICE_TYPE_GO', ''),
        'IM' => env('REMITA_SERVICE_TYPE_IM', ''),
        'JI' => env('REMITA_SERVICE_TYPE_JI', ''),
        'KD' => env('REMITA_SERVICE_TYPE_KD', ''),
        'KN' => env('REMITA_SERVICE_TYPE_KN', ''),
        'KT' => env('REMITA_SERVICE_TYPE_KT', ''),
        'KE' => env('REMITA_SERVICE_TYPE_KE', ''),
        'KO' => env('REMITA_SERVICE_TYPE_KO', ''),
        'KW' => env('REMITA_SERVICE_TYPE_KW', ''),
        'LA' => env('REMITA_SERVICE_TYPE_LA', ''),
        'NA' => env('REMITA_SERVICE_TYPE_NA', ''),
        'NI' => env('REMITA_SERVICE_TYPE_NI', ''),
        'OG' => env('REMITA_SERVICE_TYPE_OG', ''),
        'ON' => env('REMITA_SERVICE_TYPE_ON', ''),
        'OS' => env('REMITA_SERVICE_TYPE_OS', ''),
        'OY' => env('REMITA_SERVICE_TYPE_OY', ''),
        'PL' => env('REMITA_SERVICE_TYPE_PL', ''),
        'RI' => env('REMITA_SERVICE_TYPE_RI', ''),
        'SO' => env('REMITA_SERVICE_TYPE_SO', ''),
        'TA' => env('REMITA_SERVICE_TYPE_TA', ''),
        'YO' => env('REMITA_SERVICE_TYPE_YO', ''),
        'ZA' => env('REMITA_SERVICE_TYPE_ZA', ''),
        'FC' => env('REMITA_SERVICE_TYPE_FC', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Helper: flat state list for dropdowns
    |--------------------------------------------------------------------------
    */

    'state_options' => [
        'AB' => 'Abia', 'AD' => 'Adamawa', 'AK' => 'Akwa Ibom', 'AN' => 'Anambra',
        'BA' => 'Bauchi', 'BY' => 'Bayelsa', 'BE' => 'Benue', 'BO' => 'Borno',
        'CR' => 'Cross River', 'DE' => 'Delta', 'EB' => 'Ebonyi', 'ED' => 'Edo',
        'EK' => 'Ekiti', 'EN' => 'Enugu', 'GO' => 'Gombe', 'IM' => 'Imo',
        'JI' => 'Jigawa', 'KD' => 'Kaduna', 'KN' => 'Kano', 'KT' => 'Katsina',
        'KE' => 'Kebbi', 'KO' => 'Kogi', 'KW' => 'Kwara', 'LA' => 'Lagos',
        'NA' => 'Nasarawa', 'NI' => 'Niger', 'OG' => 'Ogun', 'ON' => 'Ondo',
        'OS' => 'Osun', 'OY' => 'Oyo', 'PL' => 'Plateau', 'RI' => 'Rivers',
        'SO' => 'Sokoto', 'TA' => 'Taraba', 'YO' => 'Yobe', 'ZA' => 'Zamfara',
        'FC' => 'FCT',
    ],
];
