<?php

/**
 * Scoring engine for the stress check system
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 */
class Mhlw_Stress_Check_Scoring {

	/**
	 * Calculate scores for all domains and determine high-stress status
	 *
	 * @since    1.0.0
	 * @param    array    $responses    Array of question_number => response_value
	 * @return   array    Calculated scores and high-stress determination
	 */
	public static function calculate_scores($responses) {
		$config = Mhlw_Stress_Check_Config::get_scoring_config();
		$questions = Mhlw_Stress_Check_Config::get_questions();

		$domain_scores = array(
			'A' => 0,
			'B' => 0,
			'C' => 0,
		);

		$scale_scores = array();
		$scales = Mhlw_Stress_Check_Config::get_scales();

		// Initialize scale scores
		foreach ($scales as $scale_key => $scale_config) {
			$scale_scores[$scale_key] = array(
				'total' => 0,
				'count' => 0,
				'average' => 0,
			);
		}

		// Process each response
		foreach ($responses as $question_number => $response_value) {
			if (!isset($questions[$question_number])) {
				continue;
			}

			$question = $questions[$question_number];
			$domain = $question['domain'];

			// Apply reverse scoring if needed
			if ($question['reverse']) {
				$scored_value = Mhlw_Stress_Check_Config::apply_reverse_scoring($response_value);
			} else {
				$scored_value = $response_value;
			}

			// Add to domain score
			$domain_scores[$domain] += $scored_value;

			// Add to scale scores
			foreach ($scales as $scale_key => $scale_config) {
				if (in_array($question_number, $scale_config['items'])) {
					$scale_scores[$scale_key]['total'] += $scored_value;
					$scale_scores[$scale_key]['count']++;
				}
			}
		}

		// Calculate scale averages
		foreach ($scale_scores as $scale_key => $scale_data) {
			if ($scale_data['count'] > 0) {
				$scale_scores[$scale_key]['average'] = round($scale_data['total'] / $scale_data['count'], 2);
			}
		}

		// Determine high-stress status
		$is_high_stress = self::determine_high_stress($domain_scores, $config['high_stress_criteria']);

		return array(
			'domain_scores' => $domain_scores,
			'scale_scores' => $scale_scores,
			'is_high_stress' => $is_high_stress,
			'criteria_met' => $is_high_stress ? self::get_met_criteria($domain_scores, $config['high_stress_criteria']) : array(),
		);
	}

	/**
	 * Determine if a respondent is classified as high-stress
	 *
	 * @since    1.0.0
	 * @param    array    $domain_scores    Array of domain => score
	 * @param    array    $criteria         High-stress criteria configuration
	 * @return   bool     True if high-stress, false otherwise
	 */
	private static function determine_high_stress($domain_scores, $criteria) {
		// Criterion (a): Domain B ≥ 77
		if ($domain_scores['B'] >= $criteria['criterion_a']['threshold']) {
			return true;
		}

		// Criterion (b): (Domain A + Domain C) ≥ 76 AND Domain B ≥ 63
		$combined_ac = $domain_scores['A'] + $domain_scores['C'];
		if ($combined_ac >= $criteria['criterion_b']['combined_threshold'] &&
			$domain_scores['B'] >= $criteria['criterion_b']['domain_b_threshold']) {
			return true;
		}

		return false;
	}

	/**
	 * Get which criteria were met for high-stress classification
	 *
	 * @since    1.0.0
	 * @param    array    $domain_scores    Array of domain => score
	 * @param    array    $criteria         High-stress criteria configuration
	 * @return   array    Array of met criteria descriptions
	 */
	private static function get_met_criteria($domain_scores, $criteria) {
		$met_criteria = array();

		// Check Criterion (a)
		if ($domain_scores['B'] >= $criteria['criterion_a']['threshold']) {
			$met_criteria[] = __('Criterion (a): Domain B score indicates high stress', 'mhlw-compliant-stress-check-system');
		}

		// Check Criterion (b)
		$combined_ac = $domain_scores['A'] + $domain_scores['C'];
		if ($combined_ac >= $criteria['criterion_b']['combined_threshold'] &&
			$domain_scores['B'] >= $criteria['criterion_b']['domain_b_threshold']) {
			$met_criteria[] = __('Criterion (b): Combined Domain A+C and Domain B scores indicate high stress', 'mhlw-compliant-stress-check-system');
		}

		return $met_criteria;
	}

	/**
	 * Get advice text based on high-stress classification
	 *
	 * @since    1.0.0
	 * @param    bool     $is_high_stress    Whether respondent is high-stress
	 * @return   string   Advice text
	 */
	public static function get_advice($is_high_stress) {
		if ($is_high_stress) {
			return __(
				'Your stress check results indicate that you may be experiencing high levels of work-related stress. ' .
				'We recommend that you consult with the Industrial Physician or designated personnel for follow-up support. ' .
				'Please take care of your health and consider discussing your workload and work environment with your supervisor.',
				'mhlw-compliant-stress-check-system'
			);
		} else {
			return __(
				'Your stress check results do not indicate high levels of work-related stress. ' .
				'Continue to maintain a healthy work-life balance and utilize available support resources when needed.',
				'mhlw-compliant-stress-check-system'
			);
		}
	}

	/**
	 * Get scale name by key
	 *
	 * @since    1.0.0
	 * @param    string   $scale_key    Scale identifier
	 * @return   string   Scale name
	 */
	public static function get_scale_name($scale_key) {
		$scales = Mhlw_Stress_Check_Config::get_scales();
		return isset($scales[$scale_key]['name']) ? $scales[$scale_key]['name'] : $scale_key;
	}

	/**
	 * Calculate group statistics for an organization/department
	 *
	 * @since    1.0.0
	 * @param    array    $response_ids    Array of response IDs to analyze
	 * @return   array    Group statistics
	 */
	public static function calculate_group_statistics($response_ids) {
		global $wpdb;

		if (empty($response_ids)) {
			return array();
		}

		$ids_placeholder = implode(',', array_map('intval', $response_ids));

		// Get all responses
		$table_responses = $wpdb->prefix . 'mhlw_stress_responses';
		$sql = "SELECT * FROM $table_responses WHERE id IN ($ids_placeholder) AND status = 'completed'";
		$responses = $wpdb->get_results($sql, ARRAY_A);

		if (empty($responses)) {
			return array();
		}

		$total_respondents = count($responses);
		$high_stress_count = 0;
		$domain_a_scores = array();
		$domain_b_scores = array();
		$domain_c_scores = array();

		foreach ($responses as $response) {
			if ($response['is_high_stress']) {
				$high_stress_count++;
			}
			$domain_a_scores[] = $response['domain_a_score'];
			$domain_b_scores[] = $response['domain_b_score'];
			$domain_c_scores[] = $response['domain_c_score'];
		}

		return array(
			'total_respondents' => $total_respondents,
			'high_stress_count' => $high_stress_count,
			'high_stress_percentage' => round(($high_stress_count / $total_respondents) * 100, 1),
			'domain_a_average' => round(array_sum($domain_a_scores) / count($domain_a_scores), 2),
			'domain_b_average' => round(array_sum($domain_b_scores) / count($domain_b_scores), 2),
			'domain_c_average' => round(array_sum($domain_c_scores) / count($domain_c_scores), 2),
			'participation_rate' => null, // To be calculated separately with eligible count
		);
	}

}
