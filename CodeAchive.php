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
}