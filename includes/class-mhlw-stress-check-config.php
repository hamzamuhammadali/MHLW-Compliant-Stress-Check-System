<?php

/**
 * Configuration class for the 57-question stress check system
 *
 * @since      1.0.0
 * @package    Mhlw_Compliant_Stress_Check_System
 * @subpackage Mhlw_Compliant_Stress_Check_System/includes
 */
class Mhlw_Stress_Check_Config {

	/**
	 * Get all 57 questions with their configuration
	 *
	 * @since    1.0.0
	 * @return   array    Array of questions with metadata
	 */
	public static function get_questions() {
		return array(
			// Domain A: Job Stressors (Questions 1-17)
			1  => array('text' => __('I have to do an extremely large amount of work.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			2  => array('text' => __('I cannot finish my work within working hours.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			3  => array('text' => __('I have to work very hard.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			4  => array('text' => __('My job requires a great deal of concentration.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			5  => array('text' => __('My job is difficult and requires advanced knowledge and skills.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			6  => array('text' => __('During working hours, I must constantly think about work.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			7  => array('text' => __('My job requires a great deal of physical effort.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			8  => array('text' => __('I can work at my own pace.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => true),
			9  => array('text' => __('I can decide the order and method of my work on my own.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => true),
			10 => array('text' => __('I can reflect my opinions in workplace policies.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => true),
			11 => array('text' => __('I have few opportunities to use my skills or knowledge at work.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			12 => array('text' => __('My job is not well suited to me.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			13 => array('text' => __('There are disagreements within my department.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			14 => array('text' => __('The atmosphere in my workplace is friendly.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => true),
			15 => array('text' => __('The working environment in my workplace (noise, lighting, temperature, ventilation, etc.) is poor.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => false),
			16 => array('text' => __('My job is meaningful.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => true),
			17 => array('text' => __('I feel that my work is worthwhile.', 'mhlw-compliant-stress-check-system'), 'domain' => 'A', 'reverse' => true),

			// Domain B: Stress Reactions (Questions 18-46)
			18 => array('text' => __('I feel full of energy.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			19 => array('text' => __('I feel lively.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			20 => array('text' => __('I feel enthusiastic about working.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			21 => array('text' => __('I feel extremely tired.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			22 => array('text' => __('I feel exhausted.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			23 => array('text' => __('I feel listless.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			24 => array('text' => __('I feel irritated.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			25 => array('text' => __('I feel angry.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			26 => array('text' => __('I feel easily annoyed.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			27 => array('text' => __('I feel anxious.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			28 => array('text' => __('I feel uneasy.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			29 => array('text' => __('I feel restless.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			30 => array('text' => __('I feel depressed.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			31 => array('text' => __('I feel gloomy.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			32 => array('text' => __('I feel blue.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			33 => array('text' => __('I have headaches or feel heaviness in my head.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			34 => array('text' => __('I feel stiff in my shoulders or have neck pain.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			35 => array('text' => __('I have lower back pain.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			36 => array('text' => __('I feel eye strain.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			37 => array('text' => __('I experience palpitations or shortness of breath.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			38 => array('text' => __('I have stomach or intestinal problems.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			39 => array('text' => __('I have poor appetite.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			40 => array('text' => __('I have difficulty sleeping.', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => false),
			41 => array('text' => __('How reliable is your supervisor when you are troubled?', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			42 => array('text' => __('How well does your supervisor listen to you when you consult them?', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			43 => array('text' => __('How much support does your supervisor provide when you are having difficulties?', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			44 => array('text' => __('How reliable are your coworkers when you are troubled?', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			45 => array('text' => __('How well do your coworkers listen to you when you consult them?', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),
			46 => array('text' => __('How much support do your coworkers provide when you are having difficulties?', 'mhlw-compliant-stress-check-system'), 'domain' => 'B', 'reverse' => true),

			// Domain C: Social Support & Other Factors (Questions 47-57)
			47 => array('text' => __('How reliable are your family members or friends when you are troubled?', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			48 => array('text' => __('How well do your family members or friends listen to you when you consult them?', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			49 => array('text' => __('How much support do your family members or friends provide when you are having difficulties?', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			50 => array('text' => __('I am satisfied with my job.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			51 => array('text' => __('I am satisfied with my family life.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			52 => array('text' => __('I have been bullied or harassed at work.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => false),
			53 => array('text' => __('I am satisfied with my work performance.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			54 => array('text' => __('I feel my job is secure.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			55 => array('text' => __('I am worried about losing my job.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => false),
			56 => array('text' => __('I am satisfied with my workplace.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
			57 => array('text' => __('I am satisfied with my overall life.', 'mhlw-compliant-stress-check-system'), 'domain' => 'C', 'reverse' => true),
		);
	}

	/**
	 * Get response options (4-point scale)
	 *
	 * @since    1.0.0
	 * @return   array    Response options with values
	 */
	public static function get_response_options() {
		return array(
			1 => __('No', 'mhlw-compliant-stress-check-system'),
			2 => __('Slightly No', 'mhlw-compliant-stress-check-system'),
			3 => __('Mostly Yes', 'mhlw-compliant-stress-check-system'),
			4 => __('Yes', 'mhlw-compliant-stress-check-system'),
		);
	}

	/**
	 * Get scoring configuration for high-stress determination
	 *
	 * Based on the requirements document:
	 * - Domain A: Items 1-17 (max score: 17 items × 4 points = 68)
	 * - Domain B: Items 18-46 (max score: 29 items × 4 points = 116)
	 * - Domain C: Items 47-57 (max score: 11 items × 4 points = 44)
	 *
	 * High-stress criteria:
	 * - Criterion (a): Domain B ≥ 77 (out of 116)
	 * - Criterion (b): (Domain A + Domain C) ≥ 76 (out of 104) AND Domain B ≥ 63
	 *
	 * @since    1.0.0
	 * @return   array    Scoring configuration
	 */
	public static function get_scoring_config() {
		return array(
			'domains' => array(
				'A' => array(
					'items' => range(1, 17),
					'max_score' => 68,
					'reverse_items' => array(8, 9, 10, 14, 16, 17),
				),
				'B' => array(
					'items' => range(18, 46),
					'max_score' => 116,
					'reverse_items' => array(18, 19, 20, 41, 42, 43, 44, 45, 46),
				),
				'C' => array(
					'items' => range(47, 57),
					'max_score' => 44,
					'reverse_items' => array(47, 48, 49, 50, 51, 53, 54, 56, 57),
				),
			),
			'high_stress_criteria' => array(
				'criterion_a' => array(
					'domain' => 'B',
					'threshold' => 77,
				),
				'criterion_b' => array(
					'combined_domains' => array('A', 'C'),
					'combined_threshold' => 76,
					'domain_b_threshold' => 63,
				),
			),
		);
	}

	/**
	 * Apply reverse scoring to a response value
	 *
	 * @since    1.0.0
	 * @param    int    $value    Original response value (1-4)
	 * @return   int    Reversed score (1→4, 2→3, 3→2, 4→1)
	 */
	public static function apply_reverse_scoring($value) {
		$reverse_map = array(1 => 4, 2 => 3, 3 => 2, 4 => 1);
		return isset($reverse_map[$value]) ? $reverse_map[$value] : $value;
	}

	/**
	 * Get scale definitions for group analysis
	 *
	 * @since    1.0.0
	 * @return   array    Scale definitions
	 */
	public static function get_scales() {
		return array(
			'psychological_job_demand' => array(
				'name' => __('Psychological Job Demand', 'mhlw-compliant-stress-check-system'),
				'items' => array(1, 2, 3, 4, 5, 6),
			),
			'physical_job_demand' => array(
				'name' => __('Physical Job Demand', 'mhlw-compliant-stress-check-system'),
				'items' => array(7),
			),
			'job_control' => array(
				'name' => __('Job Control', 'mhlw-compliant-stress-check-system'),
				'items' => array(8, 9, 10),
			),
			'skill_utilization' => array(
				'name' => __('Skill Utilization', 'mhlw-compliant-stress-check-system'),
				'items' => array(11, 12),
			),
			'workplace_environment' => array(
				'name' => __('Workplace Environment', 'mhlw-compliant-stress-check-system'),
				'items' => array(13, 14, 15),
			),
			'job_satisfaction' => array(
				'name' => __('Job Satisfaction', 'mhlw-compliant-stress-check-system'),
				'items' => array(16, 17),
			),
			'vigor' => array(
				'name' => __('Vigor', 'mhlw-compliant-stress-check-system'),
				'items' => array(18, 19, 20),
			),
			'fatigue' => array(
				'name' => __('Fatigue', 'mhlw-compliant-stress-check-system'),
				'items' => array(21, 22, 23),
			),
			'anger' => array(
				'name' => __('Anger', 'mhlw-compliant-stress-check-system'),
				'items' => array(24, 25, 26),
			),
			'anxiety' => array(
				'name' => __('Anxiety', 'mhlw-compliant-stress-check-system'),
				'items' => array(27, 28, 29),
			),
			'depression' => array(
				'name' => __('Depression', 'mhlw-compliant-stress-check-system'),
				'items' => array(30, 31, 32),
			),
			'physical_symptoms' => array(
				'name' => __('Physical Symptoms', 'mhlw-compliant-stress-check-system'),
				'items' => array(33, 34, 35, 36, 37, 38, 39, 40),
			),
			'supervisor_support' => array(
				'name' => __('Supervisor Support', 'mhlw-compliant-stress-check-system'),
				'items' => array(41, 42, 43),
			),
			'coworker_support' => array(
				'name' => __('Coworker Support', 'mhlw-compliant-stress-check-system'),
				'items' => array(44, 45, 46),
			),
			'family_friend_support' => array(
				'name' => __('Family and Friend Support', 'mhlw-compliant-stress-check-system'),
				'items' => array(47, 48, 49),
			),
		);
	}

	/**
	 * Get the minimum group size for analysis display
	 *
	 * @since    1.0.0
	 * @return   int    Minimum number of respondents
	 */
	public static function get_minimum_group_size() {
		return 10;
	}

	/**
	 * Get domain labels for display
	 *
	 * @since    1.0.0
	 * @return   array    Domain labels
	 */
	public static function get_domain_labels() {
		return array(
			'A' => __('Domain A: Job Stressors', 'mhlw-compliant-stress-check-system'),
			'B' => __('Domain B: Stress Reactions', 'mhlw-compliant-stress-check-system'),
			'C' => __('Domain C: Social Support', 'mhlw-compliant-stress-check-system'),
		);
	}

	/**
	 * Get the domain for a specific question
	 *
	 * @since    1.0.0
	 * @param    int    $question_number    Question number (1-57)
	 * @return   string    Domain letter (A, B, or C)
	 */
	public static function get_question_domain($question_number) {
		$questions = self::get_questions();
		return isset($questions[$question_number]) ? $questions[$question_number]['domain'] : '';
	}

}
