<?php

return array(
    'US' => array(
        'target'          => 'Production',
        'version'         => '2007',
        'primaryLangID'   => 'EN',
        'requestorIdType' => '4',
        'accountNumber'   => '4008E048D9F54d3b-45526445', // @TODO :  Put the actual account number
        'coverageType'    => '121212121',
        'url'             => 'http://ota.thrifty.com/OTA/2010A/'
//	    'url'			   => 'http://ota.thrifty.com/OTA/2010A/'    
    ),
    'AU' => array(
        'target'          => 'Test',
        'version'         => '2007',
        'primaryLangID'   => 'EN',
        'requestorIdType' => '4',
        'accountNumber'   => '91201504', // @TODO :  Put the actual account number
        'coverageType'    => '121212121',
//        'url'             => 'http://xmlwebdev.thrifty.com.au/csp/obsdevens/Thrifty.OBS.Service.WebService.cls?WSDL=1'
        'url'             => 'http://xmlweb.thrifty.com.au/thriftyens/Thrifty.OBS.Service.WebService.cls?WSDL=1'
    )
);
