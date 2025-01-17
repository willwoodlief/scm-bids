<?php

// config for Scm/ScmPluginBid


return [
    'test_pdf' => env('SCM_PLUGIN_BIDS_CHECK_PDF',\Scm\PluginBid\Models\Enums\TypeOfAcceptedFile::DEFAULT_TESTING_STATE_FOR_PDF),
];

//config('scm-plugin-bid.test_pdf') //example for accessing


