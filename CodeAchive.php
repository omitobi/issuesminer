<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 28/04/2017
 * Time: 03:35
 */

namespace App\Utilities;


class CodeAchive
{

    /*
            foreach ($revision as $key => $_revision) {
                if ($imperative = $_revision->vcsFileType->IsImperative) {
                    if (!isset($_revisions_by_imp[$date]['Avg_Previous_Imp_Commits'])) {
                        $cntplus['aic']++;
                    }
                    $_revisions_by_imp[$_revision->Date]['Avg_Previous_Imp_Commits'] = $imp_f_count['aic'] ?  $imp_f_count['aic']/$cntplus['aic'] : 0;
                    $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $cntplus['aic']/$dev_size, 'no');
//                    $_revisions_by_imp[$revision->Date][$key] = $imperative;
                    $imp_f_count['aic'] += $imperative;
                }

                if ($oo = $_revision->vcsFileType->IsOO) {
                    if (!isset($_revisions_by_imp[$date]['Avg_Previous_OO_Commits'])) {
                        $cntplus['aooc']++;
                    }
                    $_revisions_by_imp[$_revision->Date]['Avg_Previous_OO_Commits'] =  $imp_f_count['aooc'] ?  $imp_f_count['aooc']/$cntplus['aooc'] : 0;
                    $this->populateEstimations($date, 'Avg_Previous_OO_Commits', $cntplus['aooc']/$dev_size, 'no');
                    $imp_f_count['aooc'] += $oo;
                }

                if ($xml = $_revision->vcsFileType->isXML) {
                    if (!isset($_revisions_by_imp[$date]['Avg_Previous_XML_Commits'])) {
                        $cntplus['axmc']++;
                    }
                    $_revisions_by_imp[$_revision->Date]['Avg_Previous_XML_Commits'] =  $imp_f_count['axmc'] ?  $imp_f_count['axmc']/$cntplus['axmc'] : 0;
                    $this->populateEstimations($date, 'Avg_Previous_XML_Commits', $cntplus['axmc']/$dev_size, 'no');
                    $imp_f_count['axmc'] += $xml;
                }
            }*/




    /*
     * From VCSEstimationsController.php
     *
     * Key: Project Date Revision Id
     * Predicted column (needed for training as well): Project Yearly LOC Churn
     */

    function revise($revisions, $project)
    {
        $gen_count = 0;

        $imp_f_count = ['aic' => 0, 'aooc' => 0, 'axmc' => 0, 'axlc' => 0];
        $cntplus = ['aic' => 0, 'aooc' => 0, 'axmc' => 0, 'axlc' => 0];

        $_revisions_by_imp = [];
        $dev_size = 0;

//        $this->estimations = $revisions->whereDate('Date', '>', '2006-03-23 20:55:48')->maxDate('Date');
        $developers = $revisions->unique('CommitterId');
//        $mperative_commits = $revisions->where('vcsFileType.IsImperative', 1)->unique('CommitterId');
//        $this->estimations = $mperative_commits;
//        return ;
        $_revisions_by_date = $revisions->groupBy('Date');
        foreach ($_revisions_by_date as $date =>  $revision)
        {

            $this->populateEstimations( $date, 'ProjectId', $project->Id, 'normal' );
            $this->populateEstimations( $date, 'Date', $date, 'normal' );


            $dev_size =  $developers->filter( function ($devs) use ($date) {
                return CollectionUtility::whereDate( $devs->Date, '<=', $date );
            })->count();
//                $_revisions_by_imp = $revision->where('vcsFileType.IsOO', '=', 1)->unique('CommitId')->count();
//                $this->estimations = $_revisions_by_imp;
//                return false;
//                $imp_f_count = $imp_f_count + ($revision->vcsFileType->IsImperative) ? 1 : 0;
//                $this->populateEstimations($date, 'Imperative_Files', $revision->count());
//                $this->populateEstimations($date, 'ProjectDateRevisionId', 'normal' );
            $imperative_for_day = $revision->where('vcsFileType.IsImperative', 1)->count();
            $oo_for_day = $revision->where('vcsFileType.IsOO', 1)->count();
            $xml_for_day = $revision->where('vcsFileType.IsXML', 1)->count();
            $xls_for_day = $revision->where('Extension', '.xls')->count();


            $imp_f_count['aic'] += (($imperative_for_day > 0) ? 1 : 0);
            $imp_f_count['aooc'] += (($oo_for_day > 0) ? 1 : 0);
            $imp_f_count['axmc'] += (($xml_for_day > 0) ? 1 : 0);
            $imp_f_count['axlc'] += (($xls_for_day > 0) ? 1 : 0);

            $cntplus['aic'] += $imperative_for_day;
            $cntplus['aooc'] += $oo_for_day;
            $cntplus['axmc'] += $xml_for_day;
            $cntplus['axlc'] += $xls_for_day;

            $previous_commits = $revisions->where('CommitterId', $revision->last()->CommitterId)->whereDate('Date', '<', $date);
//            $xls_for_day = $revision->where('vcsFileType.IsImperative', 1)->count();

            $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $imp_f_count['aic']/$dev_size, 'on');
            $this->populateEstimations($date, 'Avg_Previous_OO_Commits', $imp_f_count['aooc']/$dev_size, 'on');
            $this->populateEstimations($date, 'Avg_Previous_XML_Commits', $imp_f_count['axmc']/$dev_size, 'on');
            $this->populateEstimations($date, 'Avg_Previous_XSL_Commits', $imp_f_count['axlc']/$dev_size, 'on');

            $this->populateEstimations($date, 'Committer_Previous_Commits', $previous_commits->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_Imp_Commits', $previous_commits->where('vcsFileType.IsImperative', 1)->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_OO_Commits', $previous_commits->where('vcsFileType.IsOO', 1)->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_XML_Commits', $previous_commits->where('vcsFileType.IsXML', 1)->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_XSL_Commits', $previous_commits->where('Extension', '.xls')->count(), 'on');
            $this->populateEstimations($date, 'Developers_On_Project_To_Date', $dev_size);

            $this->populateEstimations($date, 'Imperative_Files', $cntplus['aic'], 'abc');
            $this->populateEstimations($date, 'OO_Files', $cntplus['aooc']);
            $this->populateEstimations($date, 'Total_Developers', $dev_size);

            $this->populateEstimations($date, 'Total_XSL_Developers', 0);
            $this->populateEstimations($date, 'XML_Files', $cntplus['axmc']);

            $this->populateEstimations($date, 'XSL_Files', 0);


            $this->populateEstimations($date, 'Total_Imp_Developers',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsImperative && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsImperative && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Total_OO_Developers',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsOO && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'OO_Developers_On_Project_To_Date',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsOO && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Total_XML_Developers',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsXML && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'XML_Developers_On_Project_To_Date',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsXML && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Total_XSL_Developers',
                $revisions->where('Extension', '.js')->count(),
                'on');
            $this->populateEstimations($date, 'XSL_Developers_On_Project_To_Date', 0);



//                $this->populateEstimations($date, 'XLS_Files', $revision->where('vcsFileType.isXML', 1)->count());


        }

//        $this->insertOrUpdate(array_values($this->estimations), 'VCSEstimations');

        return $this->respond( $this->estimations );

    }



//
//
//$project->vcsFileRevisions()->orderBy('Date','asc')->with('vcsFileType')->with('vcsFileExtension')->chunk(2000, function ($revisions)
//        use (
//            &$estimations,
//            &$imp_f_count,
//            &$cntplus,
//            &$_revisions_by_imp,
//            $gen_count
//        ){
//
//            $developers = $revisions->unique('CommitterId');
////            $results = array_merge($results, $developers);
//
//            $_revisions_by_date = $revisions->groupBy('Date')->all();
//            foreach ($_revisions_by_date as $date =>  $revision)
//            {
////                $_revisions_by_imp = $revision->where('vcsFileType.IsOO', '=', 1)->unique('CommitId')->count();
////                $this->estimations = $_revisions_by_imp;
////                return false;
////                $imp_f_count = $imp_f_count + ($revision->vcsFileType->IsImperative) ? 1 : 0;
////                $this->populateEstimations($date, 'Imperative_Files', $revision->count());
////                $this->populateEstimations($date, 'ProjectDateRevisionId', 'normal' );
//                $this->populateEstimations($date, 'ProjectId', $revision->first()->ProjectId, 'normal' );
//                $this->populateEstimations($date, 'Date', $date, 'normal' );
//
//
//
//                $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', 0, 'on');
//                $this->populateEstimations($date, 'Avg_Previous_OO_Commits', 0, 'on');
//                $this->populateEstimations($date, 'Avg_Previous_XML_Commits', 0, 'on');
//                foreach ($revision as $key => $_revision) {
//                    if ($imperative = $_revision->vcsFileType->IsImperative) {
//                        if (!isset($_revisions_by_imp[$date]['Avg_Previous_Imp_Commits'])) {
//                            $cntplus['aic']++;
//                        }
//$_revisions_by_imp[$_revision->Date]['Avg_Previous_Imp_Commits'] = $imp_f_count['aic'] ?  $imp_f_count['aic']/$cntplus['aic'] : 0;
//$this->populateEstimations($date, 'Avg_Previous_Imp_Commits', ( $imp_f_count['aic'] ?  $imp_f_count['aic']/$cntplus['aic'] : 0), 'no');
////                    $_revisions_by_imp[$revision->Date][$key] = $imperative;
//$imp_f_count['aic'] += $imperative;
//}
//
//if ($oo = $_revision->vcsFileType->IsOO) {
//    if (!isset($_revisions_by_imp[$date]['Avg_Previous_OO_Commits'])) {
//        $cntplus['aooc']++;
//    }
//    $_revisions_by_imp[$_revision->Date]['Avg_Previous_OO_Commits'] =  $imp_f_count['aooc'] ?  $imp_f_count['aooc']/$cntplus['aooc'] : 0;
//    $this->populateEstimations($date, 'Avg_Previous_OO_Commits', ( $imp_f_count['aooc'] ?  $imp_f_count['aooc']/$cntplus['aooc'] : 0), 'no');
//    $imp_f_count['aooc'] += $oo;
//}
//
//if ($xml = $_revision->vcsFileType->isXML) {
//    if (!isset($_revisions_by_imp[$date]['Avg_Previous_XML_Commits'])) {
//        $cntplus['axmc']++;
//    }
//    $_revisions_by_imp[$_revision->Date]['Avg_Previous_XML_Commits'] =  $imp_f_count['axmc'] ?  $imp_f_count['axmc']/$cntplus['axmc'] : 0;
//    $this->populateEstimations($date, 'Avg_Previous_XML_Commits', ( $imp_f_count['axmc'] ?  $imp_f_count['axmc']/$cntplus['axmc'] : 0), 'no');
//    $imp_f_count['axmc'] += $xml;
//}
//}
//
//
//$this->populateEstimations($date, 'Avg_Previous_XSL_Commits', 0);
//
//$this->populateEstimations($date, 'Committer_Previous_Commits', 0);
//$this->populateEstimations($date, 'Committer_Previous_Imp_Commits', 0);
//$this->populateEstimations($date, 'Committer_Previous_OO_Commits', 0);
//$this->populateEstimations($date, 'Committer_Previous_XML_Commits', 0);
//$this->populateEstimations($date, 'Committer_Previous_XSL_Commits', 0);
//$this->populateEstimations($date, 'Developers_On_Project_To_Date', 0);
//$this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date', 0);
//$this->populateEstimations($date, 'Imperative_Files', $revision->where('vcsFileType.IsImperative', 1)->count());
//$this->populateEstimations($date, 'OO_Developers_On_Project_To_Date', 1);
//$this->populateEstimations($date, 'OO_Files', $revision->where('vcsFileType.IsOO', 1)->count());
//
//
//foreach ($developers as $developer)
//{
//    if($developer['Date'] === $date){
//        $this->populateEstimations($date, 'Total_Developers', $gen_count, 'on');
//        $gen_count++;
//    }
//
//}
//
//
//$this->populateEstimations($date, 'Total_Imp_Developers', 0);
//$this->populateEstimations($date, 'Total_OO_Developers', 0);
//$this->populateEstimations($date, 'Total_XML_Developers', 0);
//$this->populateEstimations($date, 'Total_XSL_Developers', 0);
//$this->populateEstimations($date, 'XML_Developers_On_Project_To_Date', 0);
//$this->populateEstimations($date, 'XML_Files', $revision->where('vcsFileType.isXML', 1)->count());
//$this->populateEstimations($date, 'XSL_Developers_On_Project_To_Date', 0);
//$this->populateEstimations($date, 'XSL_Files', 0);
////                $this->populateEstimations($date, 'XLS_Files', $revision->where('vcsFileType.isXML', 1)->count());
//
//
//}

//            $_revisions_by_imp = [];
//            $_revisions_by_ComitId = $revisions->groupBy('CommitId')->all();
//            foreach ($_revisions_by_ComitId as $key => $_revision)
//            {
////                $_revisions_by_imp[$_revision->Date]['isNormal'] = $key;
//                $imperative = $_revision->where('vcsFileType.IsImperative',  1);
//                if($imperative->count()){
//                    $_revisions_by_imp[$_revision->first()->Date]['isImperative'] = $imp_f_count;
////                    $_revisions_by_imp[$_revision->first()->Date][$key] = $imperative;
//                    $imp_f_count += $imperative->count();
////                    break;
//                }
//
//            }
/* $cntplus = 0;
 foreach ($revisions as $key => $_revision)
 {
     if($imperative = $_revision->vcsFileType->IsImperative){
         if(!isset($_revisions_by_imp[$_revision->Date])){
             $cntplus++;
         }
         $_revisions_by_imp[$_revision->Date]['Avg_Previous_Imp_Commits'] = $imp_f_count ?  $imp_f_count/$cntplus : 0;
         $_revisions_by_imp[$_revision->Date][$key] = $imperative;
         $imp_f_count += $imperative;

//                    break;
     }

 }
 $this->results = $_revisions_by_imp;
//            $this->results = array_where($_revisions_by_imp, function($array){
//                return $array['isImperative'] === 1;
//            });
 $estimations = $this->estimations;*/

//            $dates  = array_keys($this->estimations);



//            return false;
//            return false;
//            $others  = $this->array_extract($this->estimations, [
//                'Date',
//                'ProjectId',
//                'Avg_Previous_Imp_Commits',
//                'Avg_Previous_OO_Commits',
//                'Avg_Previous_XML_Commits',
//                'Avg_Previous_XSL_Commits',
//                'Committer_Previous_Commits',
//                'Committer_Previous_Imp_Commits',
//                'Committer_Previous_OO_Commits',
//                'Committer_Previous_XML_Commits',
//                'Committer_Previous_XSL_Commits',
//                'Developers_On_Project_To_Date',
//                'Imp_Developers_On_Project_To_Date',
//                'Imperative_Files',
//                'OO_Developers_On_Project_To_Date',
//                'OO_Files',
//                'Total_Developers',
//                'Total_Imp_Developers',
//                'Total_OO_Developers',
//                'Total_XML_Developers',
//                'Total_XSL_Developers',
//                'XML_Developers_On_Project_To_Date',
//                'XML_Files',
//                'XSL_Developers_On_Project_To_Date',
//                'XSL_Files'
//            ]);
////            $this->results = $this->updatesOrInserts($others, new VCSEstimation());

//            $this->results = $others;
//});

//        $result = $this->allOrInsert($this->estimations, new VCSEstimation());
//        if(!is_bool($result)){
//$this->insertOrUpdate(array_values($this->estimations), 'VCSEstimations');


/*
 *



public function index(Request $request)
    {
        $token =  $request->session()->get('token');
        $user_response = $this->guzzleclient->getBodyToArray(user_from_token_route, ['token' => $token]);
        if ($user_response->isNotOK()) {
            return back()->with('error', true);
        }
        $user = $user_response['body'];
        $user_names = $user['data']['name'];
        $user['data']['first_name'] = substr($user_names, 0, strripos($user_names, ' '));
        $user['data']['last_name'] = substr($user_names, strripos($user_names, ' '));
        $dt = Carbon::now();
        $expiryDate = Carbon::parse($user['data']['code'][0]['expires_at']);
        if ($dt->copy()->startOfDay()->eq($expiryDate->copy()->startOfDay())) {
            $user['data']['code']['expiredays'] = 0;
        } elseif ($dt->copy()->startOfDay()->greaterThan($expiryDate->copy()->startOfDay())) {
            $user['data']['code']['expiredays'] = $dt->diffInDays($expiryDate, false);
        } else {
            $user['data']['code']['expiredays'] = $dt->diffInDays($expiryDate, false) + 1;
        }

        $user['data']['code']['card_used'] = $user['data']['code'][0]['expires_at'] != '0000-00-00 00:00:00' ? true : false;

        $user_visits_response = $this->guzzleclient->getBodyToArray(sprintf(users_visits_route, $user['data']['id']), ['include' => 'museum']);

        $user_visits = $user_visits_response->isNotOK() ? [] : $user_visits_response['body'];

        $user_trans_response = $this->guzzleclient->getBodyToArray(sprintf(users_transactions_route, $user['data']['id']));

        $user_trans = $user_trans_response->isNotOK() ? [] : $user_trans_response['body'];

        $user_payment_cards_response = $this->guzzleclient->getBodyToArray(sprintf(users_payment_cards_route, $user['data']['id']));

        $user_pay_cards = $user_payment_cards_response->isNotOK() ? [] : $user_payment_cards_response['body'];

        $user_renewal_response = $this->guzzleclient->getBodyToArray(sprintf(users_get_renewal_route, $user['data']['id']));

        $user_renewal = $user_renewal_response->isNotOK() ? [] : $user_renewal_response['body'];
        dd($user_renewal_response, sprintf(users_get_renewal_route, $user['data']['id']));
//        dd($user_renewal_response, $user_trans, $user_visits, $user_pay_cards, sprintf(users_transactions_route, $user['data']['id']));
        return view('dashboard', compact('user', 'token', 'user_visits', 'user_trans', 'user_pay_cards', 'user_renewal'));
    }


define('base_route', 'http://api.museoliitto.dev.neocard.fi/v1/');
define('auth_route', base_route.'users/authenticate');
define('user_from_token_route', base_route.'auth/users');
define('users_route',  base_route.'users');
define('users_password_check_route',  base_route.'users/check/password');
define('users_password_set_route',  base_route.'users/password');
define('users_update_info',  base_route.'users');
define('users_logout_route',  base_route.'auth/logout');

define('users_visits_route',  base_route.'users/%s/visits');
define('users_transactions_route',  base_route.'users/%s/transactions');
define('users_payment_cards_route',  base_route.'users/%s/paymentCards');
define('users_get_renewal_route',  base_route.'users/%s/settings');

define('users_update_renewal_route',  base_route.'user/settings');
define('users_reset_password_route',  base_route.'users/reset_password');



 *
 *
 */

}