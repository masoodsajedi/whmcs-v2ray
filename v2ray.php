<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
use WHMCS\Config\Setting;
use WHMCS\Database\Capsule;

include_once 'lib/sanayi.php';

function v2ray_MetaData()
{
    return array(
        'v2ray' => 'v2ray for sanaii',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => true, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '80', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '443', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'ورود به پنل',
        'AdminSingleSignOnLabel' => 'ورود به پنل',
    );
}
function v2ray_ConfigOptions()
{
    return array(
        'inbound_id' => array(
            'Type' => 'text',
            'Size' => '100',
            'Default' => '1024',
            'Description' => 'inbound_id',
        ),
        'bw' => array(
            'Type' => 'text',
            'Size' => '100',
            'Default' => '1024',
            'Description' => 'bw in GB',
        ),
        'time' => array(
            'Type' => 'text',
            'Size' => '100',
            'Default' => '1024',
            'Description' => 'time in date',
        ),
        'sample' => array(
            'Type' => 'text',
            'Size' => '100',
            'Default' => '1024',
            'Description' => 'sample link {uuid} , {domain} , {remark}',
        ),
        'inbound_port' => array(
            'Type' => 'text',
            'Size' => '100',
            'Default' => '1024',
            'Description' => 'inboun port',
        ),
        
    );
}
function loginPanelSanayi($address, $username, $password) {
    
    $url = Setting::getValue('SystemURL');

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $address . '/login',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['username' => $username, 'password' => $password]),
        CURLOPT_COOKIEJAR => __DIR__ . '/cooke.txt'
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $response;
}
function v2ray_CreateAccount(array $params)
{
    $serverip = 'http://'.$params['serverhostname'].':443';
    $pId = $params['pid'];
    $serveraccesshash = $params['serveraccesshash'];
    $username = 'v2'.$params["serviceid"].random_int(10000,99999);
    $serviceid =$params["serviceid"];
    $inbound_id = $params["configoption1"];
    $bw = $params["configoption2"];
    $time = $params["configoption3"];
    try {
        $xui = new Sanayi($serverip,$serveraccesshash);
        $create_service = $xui->addClient( $username , $inbound_id, $time, $bw);
        $create_status = json_decode($create_service, true);
        # ---------------- check errors ---------------- #
        if ($create_status['status'] == false) {
            return  $create_service;
        }
        # ---
 
        $query = "UPDATE tblhosting SET domain = '" . $create_status['results']['remark'] .
                                 "' , username= '" . $create_status['results']['remark'] . 
                                 "' , dedicatedip= '" . $create_status['results']['id'] .
                                 "' , domain= '" . $create_status['results']['id'] .
                                 "' , subscriptionid= '" . $create_status['results']['subscribe'] . 
                                  "' WHERE id = " . $serviceid . " ; ";
        mysql_query($query);
    
        // return  $create_status['$create_status'];
        return  'success';

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function v2ray_SuspendAccount(array $params)
{  
    $serverip = 'http://'.$params['serverhostname'].':443';
    $serveraccesshash = $params['serveraccesshash'];
    $username = $params['username'];
    $inbound_id = $params["configoption1"];
    try {
    
        $xui = new Sanayi($serverip,$serveraccesshash);
        $create_service = $xui->disableClient($username, $inbound_id);
        $create_status = json_decode($create_service, true);
            # ---------------- check errors ---------------- #
            if ($create_status['status'] == false) {
                return  $create_service;
            }
            # ---
      
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function v2ray_UnsuspendAccount(array $params)
{

    $serverip = 'http://'.$params['serverhostname'].':443';
    $serveraccesshash = $params['serveraccesshash'];
    $username = $params['username'];
    $inbound_id = $params["configoption1"];
   
    try {
    $xui = new Sanayi($serverip,$serveraccesshash);
    $create_service = $xui->enableClient($username, $inbound_id);
    $create_status = json_decode($create_service, true);
        # ---------------- check errors ---------------- #
        if ($create_status['status'] == false) {
            return  $create_service;
        }
        # ---
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function v2ray_TerminateAccount(array $params)
{
    $serverip = 'http://'.$params['serverhostname'].':443';
    $serveraccesshash = $params['serveraccesshash'];
    $username = $params['username'];
    $inbound_id = $params["configoption1"];
    try {
        $xui = new Sanayi($serverip,$serveraccesshash);
        $create_service = $xui->deleteClient($username, $inbound_id);
        $create_status = json_decode($create_service, true);
            # ---------------- check errors ---------------- #
            if ($create_status['status'] == false) {
                return  $create_service;
            }
            # ---
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function v2ray_ChangePassword(array $params)
{
    try {
      return 'درحال توسعه';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}




function v2ray_Renew(array $params)
{

    $serverip = 'http://'.$params['serverhostname'].':443';
    $serveraccesshash = $params['serveraccesshash'];
    $username = $params['username'];
    $inbound_id = $params["configoption1"];
    $bw = $params["configoption2"];
    $time = $params["configoption3"];

    try {

        $xui = new Sanayi($serverip,$serveraccesshash);
        $create_service = $xui->enableClient($username, $inbound_id);
        $create_status = json_decode($create_service, true);
            # ---------------- check errors ---------------- #
            if ($create_status['status'] == false) {
                return  $create_service;
            }
            # ---

            $xui = new Sanayi($serverip,$serveraccesshash);
            $create_service = $xui->renew($username, $bw , $time , $inbound_id);
            $create_status = json_decode($create_service, true);
                # ---------------- check errors ---------------- #
                if ($create_status['status'] == false) {
                    return  $create_service;
                }
                # ---

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}


function v2ray_TestConnection(array $params)
{
    try {

        $response = loginPanelSanayi($params['serverhostname'].':'.$params['serverport'], $params['serverusername'] ,  $params['serverpassword']);
        $success = false;
        if ($response['success']) {
            $code = rand(11111111, 99999999);
            $session = str_replace([" ", "\n", "\t"], ['', '', ''], explode('session	', file_get_contents(__DIR__ . '/cooke.txt'))[1]);
            \WHMCS\Database\Capsule::table('tblservers')
            ->where('ipaddress', $params['serverip'])
            ->update([
                'accesshash' => $session,
            ]);
            $success = true;
            $errorMsg = '';
        } else {
            $success = false;  
            $errorMsg = 'حطا'; 
        } 
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'v2ray',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}


function v2ray_AdminCustomButtonArray()
{
    // return array(
    //     "ریست لینک اتصال" => "linkreset",
    //     "ریست حجم" => "resetbw",
    //     "آپدیت دستی تاریخ در سرور" => "updateclienttime",
    // );
}

function v2ray_ClientAreaCustomButtonArray()
{
    // return array(
    //     "ریست لینک اتصال" => "actionOneFunction",
    //     "خرید حجم" => "actionTwoFunction",
    // );
}


function v2ray_AdminServicesTabFields(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $response = array();

        $serverip = 'http://'.$params['serverhostname'].':443';
        $serveraccesshash = $params['serveraccesshash'];
       
        $serviceid =$params["serviceid"];
        $uuid =$params["domain"];
        $suburl =$params["subscriptionid"];
        $inbound_id = $params["configoption1"];
        $bw = $params["configoption2"];
        $time = $params["configoption3"];
        $urlsample = $params["configoption4"];
        $inbound_port = $params["configoption5"];
        $username = $params["username"];
        $encode_url = 'vless:///sdklfgjsdiofko;aelf';
       
       
            $xui = new Sanayi($serverip,$serveraccesshash);
            $create_service = $xui->getUserInfo( $username, $inbound_id);
            $create_status = json_decode($create_service, true);
            # ---------------- check errors ---------------- #
            if ($create_status['status'] == false) {
                return  $create_service;
            }
           
            $link = str_replace(
                ['{uuid}', '{domain}', '{remark}'],
                [$uuid, $params['serverhostname'].':'.$inbound_port , 'vless-'.$username], $urlsample);
            $Qr = 'https://api.qrserver.com/v1/create-qr-code/?data='.$link.'&size=800x800';
     
            # ---
            return array(
                'used BW' => ($create_status['result']['up']+$create_status['result']['down']) / 1024 / 1024 / 1024 .' GB',
                'total bw' => $create_status['result']['total']/ 1024 / 1024 / 1024 .' GB',
                'status' => $create_status['result']['enable'],
                'server expire date' => date("Y-m-d H:i:s", '1703712752000'),
                'server json response' => $create_service,
                'sub Url' => 'vless://3c9329b0-4d44-4b36-9b67-519da642e1ba@shida.netbazz.net:41005?type=tcp&path=%2F&host=divarcdn.com&headerType=http&security=none#vless-v2284380',
                'v2ray url' => $link,
                'QR code' => '<img style="display: block;-webkit-user-select: none;margin: auto;cursor: zoom-in;background-color: hsl(0, 0%, 90%);transition: background-color 300ms;" src="'.$Qr.'" width="200" height="200">',
            );
        

        // Return an array based on the function's response.
       
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return array();
}



function v2ray_ClientArea(array $params)
{
    
    $serviceAction = 'get_stats';
    $templateFile = 'templates/overview.tpl';

    $serverip = 'http://'.$params['serverhostname'].':443';
    $serveraccesshash = $params['serveraccesshash'];
    $uuid = $params["domain"];
    $suburl =$params["subscriptionid"];
    $inbound_id = $params["configoption1"];
    $urlsample = $params["configoption4"];
    $inbound_port = $params["configoption5"];
    $username = $params["username"];
   
        $xui = new Sanayi($serverip,$serveraccesshash);
        $create_service = $xui->getUserInfo( $username, $inbound_id);
        $create_status = json_decode($create_service, true);
        # ---------------- check errors ---------------- #
        if ($create_status['status'] == false) {
            return  $create_service;
        }
       
        $link = str_replace(
            ['{uuid}', '{domain}', '{remark}'],
            [$uuid, $params['serverhostname'].':'.$inbound_port , 'vless-'.$username], $urlsample);
        $Qr = 'https://api.qrserver.com/v1/create-qr-code/?data='.$link.'&size=800x800';
 
        $vararray['qr'] = $Qr;
        $vararray['link'] = $link;
        $vararray['username'] = $username;
        $vararray['totalbw'] = $create_status['result']['total']/ 1024 / 1024 / 1024 ;
        $vararray['usedbw'] = ($create_status['result']['up']+$create_status['result']['down']) / 1024 / 1024 / 1024;
   

    try {
        // Call the service's function based on the request action, using the
        // values provided by WHMCS in `$params`.
        $response = array();

        $vars = 's';
        // return array("templatefile" => "clientarea", "vars" => $ibsngvars);
        return array(
            'templatefile' => $templateFile,
            'vars' => $vararray,
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'provisioningmodule',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}
