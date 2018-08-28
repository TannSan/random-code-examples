<?php

namespace App\Http\Controllers;

use Countries;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * Displays each page from the questionnaire steps.
     *
     * @param  int  $id - The ID of the record to load, use -1 if new record
     * @param  int  $step_number - The step number in the questionnaire
     * @return \Illuminate\Http\Response
     */
    public function create($id = -1, $step_number = 1)
    {
        // If the url contains a clinic ID then attempt to load the record
        if ($id > -1) {
            $clinic = \App\ClinicData::find(intval($id));
            if ($clinic == null) {
                return abort(400);
            }

            // Check this employee is allowed to view this clinic
            $user = \Auth::user();
            if ($clinic->user_id !== $user->id && !$user->hasRole('Admin')) {
                return abort(403);
            }
        }

        // Configure each page of questions
        $step_number = intval($step_number);
        switch ($step_number) {
            case 1:
                $countries = Countries::getList('en', 'php');
                $final_year = date("Y");
                $earliest_year = $final_year - 1977; // 1977 is the year of the first successful IVF treatment
                return view('questions.step' . $step_number, compact('clinic', 'countries', 'earliest_year', 'final_year'));
                break;
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                return view('questions.step' . $step_number, compact('clinic'));
                break;
            default:
                return abort(404);
                break;
        }
    }

    /**
     * Create a new clinic record.
     * Called from the /step/1 link is opened.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'country' => 'required|max:20|not_in:-1',
            'year' => 'required|integer|not_in:-1',
            'total_ivf_cycles' => 'required|integer',
            'total_insemenation_cycles' => 'required|integer',
            'is_individual_clinic' => 'required|integer|not_in:-1',
            'is_public_clinic' => 'required|integer|not_in:-1',
        ]);

        // Create a new ClinicData record and call update with it's ID
        $clinic_data = new \App\ClinicData();
        $clinic_data->user_id = \Auth::user()->id;
        $clinic_data->save();

        $request['id'] = $clinic_data->id;
        $request['user_id'] = $clinic_data->user_id;

        return $this->update($request, $clinic_data->id, 1);
    }

    /**
     * Update a clinics data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id (Clinic ID)
     * @param  int  $step_number
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $step_number)
    {
        if (isset($request['back'])) {
            return redirect($id . '/step/' . --$step_number);
        }
        switch ($step_number) {
            case 1:
                $this->validate($request, [
                    'name' => 'required|max:255',
                    'country' => 'required|max:20|not_in:-1',
                    'year' => 'required|integer|not_in:-1',
                    'total_ivf_cycles' => 'required|integer|min:0',
                    'total_insemenation_cycles' => 'required|integer|min:0',
                    'is_individual_clinic' => 'required|integer|not_in:-1',
                    'is_public_clinic' => 'required|integer|not_in:-1',
                ]);
                break;
            case 2:
                $this->validate($request, [
                    'total_inquiries' => 'required|integer|min:0',
                    'total_women_started_treatments' => 'required|integer|min:0',
                    'total_pregnancies' => 'required|integer|min:0',
                ]);
                break;
            case 3:
                $this->validate($request, [
                    'total_pregnant_from_first_cycle' => 'required|integer|min:0',
                    'total_pregnant_from_second_cycle' => 'required|integer|min:0',
                    'total_pregnant_from_third_cycle' => 'required|integer|min:0',
                    'total_pregnant_from_further_cycles' => 'required|integer|min:0',
                ]);
                break;
            case 4:
                $this->validate($request, [
                    'total_not_pregnant_from_first_cycle' => 'required|integer|min:0',
                    'total_not_pregnant_started_second_cycle' => 'required|integer|min:0',
                    'total_not_pregnant_from_second_cycle' => 'required|integer|min:0',
                    'total_not_pregnant_started_third_cycle' => 'required|integer|min:0',
                    'total_not_pregnant_from_third_cycle' => 'required|integer|min:0',
                    'total_not_pregnant_started_further_cycles' => 'required|integer|min:0',
                ]);
                break;
            case 5:
                $this->validate($request, [
                    'referral_1' => 'integer',
                    // 'referral_other_1' => 'required_if:referral_1,69|max:255',
                    'referral_other_1' => 'max:255',
                    'referral_2' => 'integer',
                    // 'referral_other_2' => 'required_if:referral_2,69|max:255',
                    'referral_other_2' => 'max:255',
                    'referral_3' => 'integer',
                    // 'referral_other_3' => 'required_if:referral_3,69|max:255',
                    'referral_other_3' => 'max:255',
                    'dropout_factor_1' => 'integer',
                    'dropout_factor_other_1' => 'required_if:dropout_factor_1,69|max:255',
                    'dropout_factor_2' => 'integer',
                    'dropout_factor_other_2' => 'required_if:dropout_factor_2,69|max:255',
                    'dropout_factor_3' => 'integer',
                    'dropout_factor_other_3' => 'required_if:dropout_factor_3,69|max:255',
                ]);
                break;
            case 6:
                $this->validate($request, [
                    'previous_treatment_had_one_cycle' => 'integer|min:0',
                    'previous_treatment_had_two_cycles' => 'integer|min:0',
                    'previous_treatment_had_three_cycles' => 'integer|min:0',
                    'previous_treatment_had_further_cycles' => 'integer|min:0',
                ]);

                $request['completed'] = 1;

                break;
            default:
                return abort(400);
                break;
        }

        // Retrieve the clinic data with the supplied ID
        $clinic_data = \App\ClinicData::find(intval($request->input('id')));
        if ($clinic_data == null) {
            return abort(400);
        }

        // Check this employee is allowed to update this clinic and then update it
        $user = \Auth::user();
        if ($clinic_data->user_id === $user->id || $user->hasRole('Admin')) {
            $request['last_step_viewed'] = ++$step_number;
            $clinic_data->fill($request->all())->save();

            if ($step_number == 7) {
                return redirect('/report/' . $id . '/overview');
            } else {
                /**
                 * This is actually 3 & 4 but we incremented the value above
                 * Calculate the drop out percentage so it is ready for use later on in the Benchmark Chart
                 */
                if ($step_number == 4 || $step_number == 5) {
                    if ($clinic_data->total_not_pregnant_from_first_cycle == 0) {
                        $clinic_data->percent_dropouts_from_first_cycle = 0;
                    } else {
                        $clinic_data->percent_dropouts_from_first_cycle = $clinic_data->total_not_pregnant_started_second_cycle == 0 ? 100 : round(($clinic_data->total_not_pregnant_from_first_cycle - $clinic_data->total_not_pregnant_started_second_cycle) / $clinic_data->total_not_pregnant_from_first_cycle * 100);
                    }

                    if ($clinic_data->total_not_pregnant_from_second_cycle == 0) {
                        $clinic_data->percent_dropouts_from_second_cycle = 0;
                    } else {
                        $clinic_data->percent_dropouts_from_second_cycle = $clinic_data->total_not_pregnant_started_third_cycle == 0 ? 100 : round(($clinic_data->total_not_pregnant_from_second_cycle - $clinic_data->total_not_pregnant_started_third_cycle) / $clinic_data->total_not_pregnant_from_second_cycle * 100);
                    }

                    if ($clinic_data->total_not_pregnant_from_third_cycle == 0) {
                        $clinic_data->percent_dropouts_from_third_cycle = 0;
                    } else {
                        $clinic_data->percent_dropouts_from_third_cycle = $clinic_data->total_not_pregnant_started_further_cycles == 0 ? 100 : round(($clinic_data->total_not_pregnant_from_third_cycle - $clinic_data->total_not_pregnant_started_further_cycles) / $clinic_data->total_not_pregnant_from_third_cycle * 100);
                    }

                    $clinic_data->save();
                }
                return redirect($id . '/step/' . $step_number);
            }
        } else {
            return abort(403);
        }
    }
}
