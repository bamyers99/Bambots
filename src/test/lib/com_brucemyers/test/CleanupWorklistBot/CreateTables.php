<?php
/**
 Copyright 2014 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

namespace com_brucemyers\test\CleanupWorklistBot;

use PDO;
use Mock;

class CreateTables
{
    var $mediawiki;

	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_tools)
    {
    	// tools
    	new \com_brucemyers\CleanupWorklistBot\CreateTables($dbh_tools);

   		$dbh_tools->exec('TRUNCATE history');
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2010-11-20', 6936, 1429, 2336, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2010-11-27', 6959, 1425, 2334, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2010-12-04', 7023, 1422, 2319, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2010-12-11', 8597, 2224, 3569, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2010-12-18', 8602, 2227, 3588, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2010-12-25', 8615, 2219, 3576, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-01-01', 8638, 2193, 3525, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-01-09', 8670, 2190, 3520, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-01-16', 8709, 2137, 3388, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-01-23', 8747, 2148, 3414, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-01-30', 8765, 2145, 3412, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-02-06', 8786, 2155, 3431, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-02-13', 8806, 2157, 3437, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-02-20', 8822, 2191, 3521, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-02-28', 8840, 2189, 3525, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-03-07', 8862, 2177, 3506, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-03-14', 8880, 2178, 3507, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-03-21', 8893, 2184, 3522, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-03-28', 8902, 2187, 3530, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-04-04', 8908, 2193, 3534, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-04-11', 8917, 2199, 3539, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-04-18', 8926, 2210, 3554, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-04-25', 8936, 2204, 3542, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-05-02', 8986, 2208, 3559, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-05-09', 9010, 2203, 3549, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-05-16', 9021, 2213, 3554, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-05-23', 9028, 2212, 3564, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-05-30', 9043, 2208, 3565, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-06-06', 9059, 2218, 3581, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-06-13', 9069, 2213, 3574, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-06-20', 9079, 2215, 3581, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-06-27', 9085, 2213, 3573, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-07-04', 9095, 2227, 3608, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-07-11', 9105, 2264, 3651, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-07-18', 9114, 2269, 3650, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-07-25', 9120, 2265, 3643, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-08-01', 9130, 2264, 3640, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-08-08', 9149, 2261, 3625, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-08-15', 9161, 2260, 3619, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-08-22', 9173, 2260, 3624, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-08-29', 9178, 2246, 3613, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-09-05', 9193, 2256, 3627, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-09-12', 9203, 2242, 3605, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-09-19', 9207, 2208, 3561, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-09-26', 9212, 2210, 3564, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-10-03', 9223, 2212, 3568, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-10-10', 9227, 2215, 3576, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-10-18', 9243, 2221, 3581, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-10-28', 9254, 2226, 3600, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-11-05', 9268, 2227, 3607, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-11-12', 9278, 2232, 3614, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-11-19', 9294, 2866, 4442, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-11-26', 9302, 2862, 4438, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-12-04', 9330, 2869, 4452, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-12-12', 9341, 2866, 4453, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-12-19', 9354, 2869, 4455, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2011-12-26', 9369, 2867, 4457, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-01-02', 9392, 2838, 4416, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-01-09', 9411, 2851, 4449, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-01-16', 9475, 2849, 4439, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-01-23', 9483, 2869, 4466, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-01-30', 9493, 2862, 4459, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-02-06', 9497, 2858, 4437, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-02-13', 9505, 2869, 4458, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-02-20', 9516, 2865, 4448, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-02-27', 9525, 2865, 4454, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-03-05', 9533, 2823, 4402, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-03-12', 9543, 2818, 4407, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-03-19', 9740, 2809, 4419, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-04-04', 9770, 2818, 4438, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-04-11', 9790, 2777, 4398, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-04-18', 9800, 2769, 4392, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-04-25', 9815, 2759, 4385, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-05-02', 9828, 2759, 4387, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-05-09', 9837, 2756, 4375, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-05-16', 9853, 2745, 4354, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-05-23', 9877, 2747, 4359, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-05-30', 9886, 2756, 4374, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-06-06', 9903, 2761, 4381, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-06-15', 9931, 2775, 4344, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-06-24', 9961, 2767, 4327, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-07-11', 10018, 2795, 4425, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-07-18', 10023, 2801, 4434, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-07-25', 10029, 2802, 4438, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-08-01', 10032, 2805, 4443, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-08-18', 10066, 2816, 4455, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-08-25', 10076, 2816, 4454, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-09-04', 10097, 2828, 4482, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-09-11', 10104, 2825, 4485, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-09-23', 10111, 2811, 4436, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-10-01', 10117, 2811, 4430, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-10-09', 10129, 2816, 4442, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-10-16', 10138, 2813, 4436, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-10-25', 10149, 2823, 4454, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-11-01', 10158, 2814, 4435, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-11-08', 10180, 2810, 4450, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-11-15', 10201, 2801, 4446, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-11-22', 10216, 2802, 4456, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-11-29', 10225, 2800, 4446, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-12-06', 10233, 2803, 4446, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-12-13', 10251, 2799, 4434, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-12-20', 10262, 2808, 4450, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2012-12-27', 10267, 2815, 4470, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-01-03', 10283, 2845, 4524, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-01-10', 10288, 2824, 4466, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-01-17', 10299, 2828, 4459, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-01-24', 10307, 2827, 4457, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-01-31', 10319, 2837, 4478, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-02-07', 10329, 2838, 4474, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-02-14', 10336, 2851, 4509, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-02-21', 10348, 2859, 4516, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-02-28', 10361, 2868, 4542, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-03-07', 10382, 2873, 4551, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-03-14', 10405, 2883, 4565, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-03-29', 10427, 2885, 4575, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-04-05', 10774, 3037, 4827, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-04-12', 10900, 3082, 4890, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-04-19', 10917, 3077, 4902, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-04-26', 10942, 3073, 4890, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-05-03', 10941, 3072, 4893, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-05-20', 10964, 3075, 4893, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-05-27', 10977, 3020, 4821, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-06-04', 10987, 3019, 4826, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-06-11', 10996, 3021, 4834, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-06-18', 11003, 3024, 4848, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-06-25', 11009, 3029, 4860, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-07-02', 11017, 3023, 4854, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-07-09', 11024, 3023, 4848, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-07-16', 11031, 3020, 4828, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-07-23', 11045, 3027, 4852, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-07-30', 11055, 3032, 4870, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-08-06', 11065, 3035, 4865, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-08-13', 11075, 3033, 4875, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-08-20', 11088, 3035, 4889, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-08-27', 11101, 3008, 4828, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-09-04', 11154, 2982, 4762, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-09-11', 11166, 2986, 4767, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-09-18', 11193, 2990, 4771, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-09-25', 11208, 2982, 4764, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-10-02', 11215, 2989, 4772, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-10-09', 11226, 2991, 4776, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-10-16', 11241, 2988, 4780, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-10-23', 11251, 2986, 4784, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-11-01', 11269, 2978, 4763, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-11-09', 11299, 2991, 4799, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-11-16', 11313, 3012, 4828, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-11-23', 11327, 3018, 4840, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-11-30', 11340, 3017, 4846, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-12-07', 11559, 3065, 4910, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-12-14', 12580, 3422, 5528, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-12-21', 12887, 3534, 5719, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2013-12-28', 12924, 3535, 5734, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-01-04', 12937, 3516, 5706, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-01-11', 12946, 3517, 5708, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-01-19', 12955, 3511, 5707, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-01-26', 12965, 3507, 5701, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-02-12', 12997, 3495, 5684, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-02-19', 13007, 3494, 5677, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-02-26', 13024, 3501, 5683, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-03-05', 13034, 3492, 5661, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-03-12', 13045, 3500, 5663, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-03-19', 13064, 3515, 5709, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-03-26', 13069, 3521, 5721, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-04-02', 13073, 3508, 5703, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-04-09', 13075, 3512, 5703, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-04-16', 13083, 3513, 5708, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-04-23', 13092, 3523, 5719, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-04-30', 13102, 3529, 5734, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-07', 13136, 3538, 5763, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-14', 13141, 3542, 5766, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-21', 13146, 3542, 5771, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-28', 13153, 3862, 6440, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-28', 13153, 3862, 6440, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-31', 13155, 3854, 6435, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-31', 13155, 3854, 6435, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-31', 13155, 3854, 6435, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-31', 13155, 3854, 6435, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-06-10', 13167, 3842, 6421, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-06-17', 13174, 3853, 6445, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-06-24', 13210, 3834, 6443, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-07-01', 13212, 3832, 6441, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-07-01', 13214, 3837, 6443, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-07-08', 13218, 3842, 6456, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-07-08', 13219, 3843, 6457, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-07-15', 13231, 3837, 6457, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-07-22', 13381, 3878, 6508, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-07-29', 13565, 3883, 6516, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-08-05', 13582, 3882, 6513, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-08-12', 13596, 3885, 6523, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-08-19', 13602, 3896, 6535, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-08-26', 13614, 3881, 6508, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-09-02', 13624, 3883, 6515, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-09-09', 13634, 3884, 6527, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-09-16', 13647, 3883, 6547, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-09-23', 13659, 3876, 6533, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-09-30', 13666, 3876, 6534, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-10-08', 13670, 3869, 6515, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-10-14', 13669, 3864, 6522, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-10-22', 13759, 3896, 6550, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-10-28', 13766, 3888, 6536, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-11-04', 13807, 3910, 6585, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-11-11', 13830, 3907, 6600, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-11-18', 13839, 3904, 6596, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-11-25', 13846, 3904, 6593, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-12-04', 13851, 3904, 6596, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-12-09', 13855, 3910, 6604, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-12-16', 13859, 3913, 6611, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-12-23', 13885, 3916, 6605, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-12-30', 13902, 3917, 6602, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-01-06', 13924, 3953, 6653, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-01-13', 14013, 3951, 6635, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-01-20', 14020, 3955, 6635, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-01-27', 14023, 4065, 6840, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-02-03', 14022, 4068, 6842, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-02-10', 14034, 4075, 6844, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-02-17', 14042, 4084, 6884, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-02-24', 14052, 4077, 6879, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-03-03', 14062, 4078, 6881, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-03-10', 14067, 4080, 6888, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-03-17', 14078, 4075, 6877, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-03-24', 14109, 4108, 6916, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-03-31', 14130, 4123, 6935, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-04-07', 14147, 4133, 6941, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-04-14', 14167, 4114, 6925, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-04-21', 14180, 4115, 6946, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-04-28', 14187, 4123, 6955, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-05-05', 14190, 4117, 6944, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-05-12', 14228, 4117, 6952, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-05-19', 14235, 4128, 6958, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-05-26', 14241, 4131, 6959, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-06-02', 14253, 4133, 6967, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-06-09', 14258, 4131, 6962, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-06-16', 14273, 4130, 6971, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-06-23', 14282, 4125, 6967, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-06-30', 14289, 4124, 6964, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-07-07', 14307, 4131, 6971, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-07-14', 14326, 4132, 6982, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-07-21', 14346, 4123, 6972, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-07-28', 14426, 3918, 6398, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-07-31', 14456, 4152, 6992, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-08-04', 14523, 4159, 7000, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-08-11', 14601, 4172, 7021, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-08-18', 14619, 4179, 7042, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-08-25', 14632, 4178, 7038, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-09-01', 14639, 4159, 6952, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-09-08', 14646, 4162, 6944, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-09-15', 14663, 4175, 6968, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-09-22', 14669, 4171, 6955, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-09-28', 14671, 4169, 6951, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-09-29', 14671, 4169, 6951, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-09-29', 14671, 4214, 7048, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-10-06', 14681, 4240, 7094, 33, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-10-13', 14693, 4246, 7102, 17, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-10-20', 14707, 4245, 7077, 18, 19)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-10-27', 14716, 4249, 7090, 13, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-11-03', 14726, 4256, 7090, 18, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-11-10', 14764, 4350, 7277, 105, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-11-17', 14817, 4354, 7279, 20, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-11-24', 14819, 4344, 7263, 10, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-12-01', 14828, 4352, 7263, 19, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-12-08', 14839, 4483, 7552, 144, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-12-15', 14858, 4443, 7452, 31, 71)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-12-22', 14858, 4445, 7450, 17, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2015-12-29', 14867, 4452, 7464, 14, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-01-05', 14893, 4440, 7451, 9, 21)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-01-12', 14901, 4420, 7374, 13, 33)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-01-19', 14909, 4397, 7325, 17, 40)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-01-26', 14960, 4382, 7289, 26, 41)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-02-02', 14966, 4357, 7244, 12, 37)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-02-09', 14966, 4353, 7238, 10, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-02-16', 14969, 4340, 7204, 10, 23)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-02-23', 14979, 5545, 8565, 1223, 18)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-03-01', 14991, 4292, 7098, 11, 1264)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-03-08', 15016, 4284, 7089, 15, 23)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-03-15', 15029, 4274, 7093, 14, 24)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-03-22', 15040, 4260, 7063, 13, 27)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-03-29', 15050, 4256, 7065, 10, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-04-05', 15066, 4258, 7080, 16, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-04-12', 15066, 4254, 7074, 18, 22)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-04-19', 15075, 4251, 7058, 12, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-04-26', 15087, 4251, 7030, 15, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-05-03', 15093, 4257, 7030, 16, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-05-10', 15107, 4253, 7022, 23, 27)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-05-17', 15120, 4249, 7019, 10, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-05-24', 15176, 4288, 7070, 49, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-05-31', 15191, 4305, 7102, 25, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-06-07', 15204, 4312, 7142, 19, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-06-14', 15214, 4322, 7180, 24, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-06-21', 15231, 4326, 7174, 16, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-06-28', 15257, 4321, 7167, 10, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-07-05', 15371, 4322, 7154, 8, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-07-12', 15480, 4322, 7144, 16, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-07-19', 15493, 4320, 7149, 8, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-07-26', 15591, 4342, 7182, 36, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-08-02', 15606, 4366, 7236, 40, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-08-09', 15634, 4400, 7299, 43, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-08-16', 15659, 4400, 7301, 11, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-08-23', 15671, 4397, 7298, 5, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-08-30', 15684, 4394, 7288, 11, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-09-06', 15695, 4394, 7300, 13, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-09-13', 16274, 4408, 7322, 20, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-09-20', 16284, 4403, 7329, 9, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-09-27', 16301, 4402, 7335, 4, 5)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-10-04', 16310, 4403, 7340, 9, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-10-11', 16320, 4412, 7354, 22, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-10-18', 16583, 4514, 7451, 113, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-10-25', 16604, 4527, 7468, 20, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-11-01', 16613, 4550, 7497, 30, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-11-08', 16622, 4570, 7520, 26, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-11-15', 16633, 4582, 7531, 24, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-11-22', 16636, 4600, 7568, 25, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-11-29', 16641, 4593, 7576, 12, 19)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-12-06', 16659, 4594, 7587, 12, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-12-13', 16662, 4598, 7588, 12, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-12-20', 16669, 4606, 7606, 18, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2016-12-27', 16681, 4610, 7607, 13, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-01-03', 16691, 4627, 7644, 27, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-01-10', 16702, 4643, 7682, 30, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-01-17', 16717, 4654, 7715, 22, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-01-24', 16741, 4658, 7713, 16, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-01-31', 16751, 4655, 7708, 7, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-02-07', 16762, 4655, 7707, 8, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-02-14', 16772, 4658, 7719, 18, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-02-21', 16779, 4659, 7725, 9, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-02-28', 16811, 4659, 7729, 11, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-03-07', 16819, 4653, 7720, 9, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-03-14', 16830, 4646, 7718, 4, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-03-21', 16851, 4650, 7728, 17, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-03-28', 16883, 4653, 7715, 42, 39)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-04-04', 16921, 4616, 7628, 27, 64)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-04-11', 16958, 4611, 7581, 38, 43)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-04-18', 16980, 4628, 7614, 24, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-04-25', 16987, 4645, 7643, 23, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-05-02', 17000, 4637, 7642, 18, 26)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-05-09', 17019, 4599, 7567, 22, 60)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-05-16', 17031, 4594, 7564, 13, 18)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-05-23', 17069, 4595, 7563, 16, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-05-30', 17082, 4604, 7581, 23, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-06-06', 17098, 4617, 7593, 29, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-06-13', 17115, 4633, 7630, 27, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-06-20', 17129, 4630, 7626, 29, 32)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-06-27', 17155, 4658, 7654, 43, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-07-04', 17167, 4645, 7643, 25, 38)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-07-11', 17180, 4643, 7631, 10, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-07-18', 17210, 4656, 7650, 20, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-07-25', 17220, 4668, 7669, 18, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-08-01', 17241, 4672, 7673, 20, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-08-08', 17259, 4673, 7690, 21, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-08-15', 17275, 4682, 7706, 18, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-08-22', 17290, 4679, 7672, 16, 19)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-08-29', 17305, 4663, 7648, 9, 25)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-09-05', 17312, 4646, 7626, 8, 25)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-09-12', 17333, 4653, 7644, 15, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-09-19', 17349, 4660, 7662, 14, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-09-26', 17347, 4655, 7662, 5, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-10-03', 17357, 4659, 7668, 15, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-10-10', 17360, 4655, 7649, 18, 22)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-10-17', 17367, 4652, 7634, 10, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-10-24', 17383, 4650, 7624, 18, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-10-31', 17392, 4647, 7630, 19, 22)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-11-07', 17412, 4664, 7659, 23, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-11-14', 17424, 4666, 7659, 13, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-11-21', 17445, 4667, 7658, 15, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-11-28', 17460, 4678, 7666, 23, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-12-05', 17472, 4664, 7634, 21, 35)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-12-12', 17488, 4676, 7657, 23, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-12-19', 17495, 4696, 7701, 31, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2017-12-26', 17517, 4711, 7728, 26, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-01-02', 17520, 4716, 7747, 18, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-01-09', 17537, 4726, 7754, 20, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-01-16', 17551, 4726, 7770, 13, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-01-23', 17568, 4737, 7795, 25, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-01-30', 17588, 4772, 7826, 57, 22)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-02-06', 17604, 4762, 7802, 23, 33)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-02-13', 17620, 4769, 7803, 18, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-02-20', 17628, 4697, 7724, 14, 86)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-02-27', 17639, 4685, 7718, 17, 29)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-02-27', 17639, 4684, 7717, 0, 1)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-03-06', 17654, 4632, 7665, 21, 73)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-03-13', 17679, 4597, 7597, 16, 51)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-03-20', 17728, 4613, 7599, 65, 49)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-03-27', 17744, 4588, 7557, 18, 43)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-04-03', 17752, 4511, 7456, 19, 96)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-04-10', 17776, 4496, 7436, 13, 28)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-04-17', 17794, 4472, 7376, 21, 45)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-04-24', 17839, 4454, 7357, 16, 34)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-05-01', 17854, 4449, 7342, 18, 23)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-05-08', 17880, 4449, 7349, 15, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-05-15', 17896, 4433, 7326, 8, 24)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-05-22', 17907, 4437, 7342, 15, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-05-29', 17923, 4440, 7328, 19, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-06-05', 17947, 4438, 7322, 18, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-06-12', 17958, 4436, 7353, 17, 19)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-06-19', 17969, 4424, 7307, 15, 27)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-06-26', 17978, 4417, 7290, 10, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-07-03', 17991, 4424, 7307, 20, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-07-10', 18002, 4427, 7315, 13, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-07-17', 18018, 4439, 7326, 24, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-07-24', 18038, 4445, 7331, 21, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-07-31', 18054, 4448, 7340, 14, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-08-07', 18057, 4434, 7357, 6, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-08-14', 18070, 4431, 7354, 15, 18)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-08-21', 18090, 4425, 7337, 11, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-08-28', 18101, 4429, 7349, 17, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-09-04', 18365, 4444, 7366, 40, 25)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-09-18', 18407, 4363, 7209, 25, 106)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-09-19', 18412, 4363, 7209, 0, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-09-25', 18421, 4367, 7224, 16, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-10-02', 18427, 4365, 7214, 20, 22)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-10-09', 18438, 4377, 7237, 19, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-10-16', 18449, 4372, 7212, 15, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-10-23', 18457, 4370, 7203, 11, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-10-30', 18468, 4369, 7189, 9, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-11-06', 18494, 4372, 7186, 15, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-11-13', 18523, 4369, 7193, 11, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-11-20', 18554, 4363, 7184, 15, 21)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-11-27', 18571, 4360, 7180, 10, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-12-04', 18587, 4364, 7190, 14, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-12-11', 18603, 4368, 7197, 37, 33)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-12-18', 18633, 4367, 7187, 9, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2018-12-25', 18648, 4367, 7190, 14, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-01-01', 18664, 4361, 7185, 5, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-01-08', 18676, 4352, 7169, 8, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-01-15', 18691, 4356, 7177, 10, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-01-22', 18699, 4358, 7176, 11, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-01-29', 18704, 4353, 7169, 6, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-02-05', 18718, 4357, 7161, 13, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-02-12', 18742, 4352, 7157, 8, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-02-19', 18754, 4402, 7226, 64, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-02-26', 18776, 4400, 7225, 9, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-03-05', 18795, 4399, 7220, 9, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-03-12', 18806, 4400, 7217, 15, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-03-19', 18822, 4397, 7206, 8, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-03-26', 18830, 4398, 7204, 6, 5)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-04-02', 18838, 4400, 7203, 7, 5)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-04-09', 18849, 4404, 7210, 11, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-04-16', 18860, 4411, 7219, 13, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-04-23', 18868, 4429, 7264, 30, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-04-30', 18876, 4413, 7246, 23, 39)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-05-07', 18890, 4426, 7260, 22, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-05-14', 18905, 4399, 7217, 15, 42)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-05-21', 18923, 4394, 7206, 15, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-05-28', 18931, 4406, 7228, 22, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-06-05', 18950, 4413, 7233, 21, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-06-11', 18953, 4423, 7253, 20, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-06-18', 18958, 4422, 7249, 13, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-06-25', 18967, 4428, 7268, 17, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-07-02', 18341, 3656, 5177, 16, 788)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-07-02', 18341, 4441, 7332, 786, 1)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-07-09', 18345, 4437, 7313, 15, 19)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-07-16', 18353, 4442, 7322, 13, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-07-23', 18363, 4438, 7301, 11, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-07-30', 18371, 4433, 7280, 12, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-08-06', 18380, 4441, 7288, 22, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-08-13', 18393, 4445, 7303, 14, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-08-20', 18403, 4443, 7313, 15, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-08-27', 18416, 4446, 7315, 17, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-09-03', 18430, 4438, 7290, 13, 21)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-09-10', 18433, 6596, 11368, 2179, 21)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-09-17', 18436, 6059, 10278, 199, 736)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-09-24', 18440, 5228, 8700, 134, 965)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-10-01', 18450, 4699, 7756, 47, 576)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-10-08', 18476, 4701, 7755, 28, 26)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-10-15', 18503, 4681, 7727, 24, 44)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-10-22', 18522, 4667, 7667, 14, 28)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-10-29', 18536, 4648, 7628, 31, 50)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-11-05', 18538, 4604, 7545, 11, 55)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-11-12', 18573, 4601, 7519, 25, 28)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-11-19', 18599, 4597, 7520, 17, 21)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-11-26', 18653, 4615, 7547, 24, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-12-03', 18725, 4619, 7549, 13, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-12-10', 18740, 4619, 7557, 10, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-12-17', 18761, 4621, 7545, 13, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-12-24', 18777, 4627, 7540, 20, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2019-12-31', 18794, 4622, 7542, 19, 24)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-01-07', 18802, 4628, 7547, 22, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-01-14', 18820, 4624, 7547, 18, 22)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-01-21', 18829, 4620, 7536, 16, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-01-28', 18842, 4617, 7540, 6, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-02-04', 18870, 4626, 7526, 18, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-02-11', 18873, 4640, 7544, 19, 5)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-02-18', 18884, 4645, 7539, 13, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-02-25', 18895, 4635, 7523, 16, 26)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-03-03', 18914, 4633, 7520, 11, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-03-10', 18921, 4638, 7529, 14, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-03-17', 18940, 4643, 7531, 13, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-03-24', 18950, 4641, 7529, 10, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-03-31', 18963, 4720, 7634, 96, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-04-07', 18980, 4742, 7652, 49, 27)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-04-14', 19023, 4716, 7629, 16, 42)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-04-21', 19028, 4721, 7649, 22, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-04-28', 19043, 4722, 7651, 19, 18)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-05-05', 19079, 4721, 7641, 25, 26)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-05-12', 19091, 4724, 7645, 19, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-05-19', 19143, 4727, 7651, 17, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-05-26', 19156, 4720, 7638, 15, 22)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-06-02', 19171, 4720, 7625, 16, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-06-09', 19207, 4721, 7635, 14, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-06-16', 19234, 4724, 7628, 16, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-06-23', 19243, 4753, 7700, 44, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-06-30', 19261, 4751, 7693, 11, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-07-07', 19266, 4751, 7688, 15, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-07-14', 19282, 4760, 7712, 23, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-07-21', 19294, 4746, 7694, 7, 21)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-07-28', 19310, 4836, 7847, 104, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-08-04', 19335, 4833, 7838, 12, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-08-11', 19349, 4825, 7822, 2, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-08-18', 19382, 4834, 7830, 19, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-08-25', 19413, 4831, 7818, 11, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-09-01', 19434, 4837, 7815, 13, 7)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-09-08', 19443, 4842, 7820, 11, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-09-15', 19451, 4852, 7825, 20, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-09-22', 19461, 4862, 7870, 30, 20)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-09-29', 19471, 4868, 7900, 18, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-10-06', 19479, 4877, 7908, 15, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-10-13', 19488, 4958, 8079, 87, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-10-20', 19494, 4996, 8114, 65, 27)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-10-27', 19502, 4997, 8118, 32, 31)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-11-03', 19512, 5016, 8138, 27, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-11-10', 19523, 5015, 8145, 16, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-11-17', 19542, 5005, 8125, 14, 24)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-11-24', 19560, 4948, 8038, 30, 87)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-12-01', 19572, 4922, 7982, 14, 40)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-12-08', 19583, 4918, 7984, 11, 15)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-12-15', 19590, 4917, 7987, 10, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-12-22', 19599, 4906, 7978, 15, 26)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2020-12-29', 19611, 4917, 7977, 25, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-01-05', 19630, 4919, 7978, 15, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-01-12', 19641, 4915, 7970, 9, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-01-19', 19674, 4920, 7975, 16, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-01-26', 19686, 4916, 7970, 9, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-02-02', 19695, 4917, 7974, 15, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-02-09', 19705, 4926, 7994, 19, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-02-16', 19711, 4933, 8003, 23, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-02-23', 19718, 4936, 8001, 11, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-03-02', 19725, 4930, 7986, 10, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-03-09', 19734, 4929, 7989, 10, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-03-16', 19743, 4920, 7977, 9, 18)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-03-23', 19753, 4916, 7966, 12, 16)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-03-30', 19757, 4915, 7960, 11, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-04-06', 19774, 4917, 7956, 12, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-04-13', 19799, 4916, 7972, 13, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-04-20', 19816, 4923, 7983, 21, 14)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-04-27', 19827, 4919, 7979, 13, 17)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-05-04', 19845, 4928, 7998, 15, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-05-11', 19881, 4946, 8032, 26, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-05-18', 19887, 4946, 8018, 11, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-05-25', 19892, 4950, 8019, 12, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-06-01', 19890, 4953, 8022, 12, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-06-08', 19894, 4956, 8035, 12, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-06-15', 19899, 4959, 8036, 11, 8)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-06-22', 19910, 4960, 8045, 13, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-06-29', 19912, 4961, 8044, 12, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-07-06', 19914, 4969, 8049, 19, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-07-13', 19920, 4968, 8042, 9, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-07-20', 19920, 4966, 8043, 8, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-07-27', 19926, 4968, 8040, 14, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-08-03', 19937, 4972, 8048, 16, 12)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-08-10', 19948, 4978, 8060, 12, 6)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-08-17', 19953, 4980, 8060, 12, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-08-24', 19955, 4978, 8053, 9, 11)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-08-31', 19965, 5303, 8412, 334, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-09-07', 19974, 4981, 8058, 15, 337)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-09-14', 19985, 4979, 8074, 7, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-09-21', 19991, 4982, 8068, 12, 9)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-09-28', 20000, 4985, 8066, 8, 5)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-10-05', 20012, 4980, 8058, 14, 19)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-10-12', 20019, 4980, 8065, 13, 13)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-10-19', 20027, 4978, 8045, 8, 10)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2021-10-26', 20037, 4988, 8060, 15, 5)");
   		
   		$dbh_tools->exec('TRUNCATE project');
   		$dbh_tools->exec("INSERT INTO project VALUES ('Featured_articles', 1, 3)");
   		$dbh_tools->exec("INSERT INTO project VALUES ('Good_article_nominees', 1, 2)");
   		$dbh_tools->exec("INSERT INTO project VALUES ('India', 1, 1)");
   		$dbh_tools->exec("INSERT INTO project VALUES ('WikiProject_Michigan', 1, 0)");

   		$dbh_tools->exec('TRUNCATE livingpeople');
   		$dbh_tools->exec("INSERT INTO livingpeople VALUES ('Earth')");

   		// enwiki
   		Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
   		$this->mediawiki = new \MockMediaWiki();

   		// category - x articles by quality (subcats)

   		$this->mediawiki->returns('getList',
   		    ['query' => ['projects' => ['Michigan' => [
   		        ['ns' => 0, 'title' =>'Michigan', 'assessment' => ['importance' => 'Top', 'class' => 'B']],
   		        ['ns' => 0, 'title' =>'Detroit, Michigan', 'assessment' => ['importance' => 'NA', 'class' => 'Unassessed']],
   		        ['ns' => 0, 'title' =>'Mackinac Island', 'assessment' => ['importance' => 'NA', 'class' => 'Unassessed']],
   		        ['ns' => 0, 'title' =>'Lansing, Michigan', 'assessment' => ['importance' => 'NA', 'class' => 'Unassessed']]
   		    ]]]],
   		    ['projectpages', ['continue' => '', 'wppprojects' => 'Michigan', 'wpplimit' => 'max', 'wppassessments' => 'true']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        ['title' =>'Category:B-Class Michigan articles', 'categoryinfo' => ['pages' => 1]],
   		        ['title' =>'Category:Unassessed Michigan articles', 'categoryinfo' => ['pages' => 3]]
   		    ]]],
   		    ['categoryinfo', ['generator' => 'categorymembers', 'gcmtitle' => 'Category:Michigan articles by quality', 'gcmtype' => 'subcat', 'gcmlimit' => 'max']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:Michigan', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:B-Class Michigan articles']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:Detroit, Michigan', 'ns' => 1],
   		        ['title' =>'Talk:Mackinac Island', 'ns' => 1],
   		        ['title' =>'Talk:Lansing, Michigan', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:Unassessed Michigan articles']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '11' => ['title' =>'Category:All articles needing coordinates', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['titles' => 'Category:All articles needing coordinates']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '12' => ['title' =>'Category:Articles needing cleanup from May 2013', 'categoryinfo' => ['pages' => 3]],
   		        '13' => ['title' =>'Category:Articles needing cleanup from March 2013', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['generator' => 'allpages', 'gapprefix' => 'Articles needing cleanup from ', 'gapnamespace' => 14, 'gaplimit' => 'max']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Michigan', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:All articles needing coordinates', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Detroit, Michigan', 'ns' => 0],
   		        ['title' =>'Lansing, Michigan', 'ns' => 0],
   		        ['title' =>'Earth', 'ns' => 0],
   		        ['title' =>'Read\'s Cavern', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Articles needing cleanup from May 2013', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Detroit, Michigan', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Articles needing cleanup from March 2013', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );


   		// category - WikiProject x articles (talk namespace)

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:India', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:WikiProject India articles']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '102' => ['title' =>'Category:Articles needing cleanup', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['titles' => 'Category:Articles needing cleanup']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'India', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Articles needing cleanup', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );


    	// category - x (talk namespace)

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:United States', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:Good article nominees']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '200' => ['title' =>'Category:Pages using citations with format and no URL', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['generator' => 'categorymembers', 'gcmtitle' => 'Category:Articles with incorrect citation syntax', 'gcmtype' => 'subcat', 'gcmlimit' => 'max']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'United States', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Pages using citations with format and no URL', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );


    	// category - x (article namespace)

    	$this->mediawiki->returns('getList',
    	    ['query' => ['categorymembers' =>  [
    	        ['title' =>'Earth', 'ns' => 0],
    	        ['title' =>'Read\'s Cavern', 'ns' => 0]
    	    ]]],
    	    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:Featured articles']]
    	    );

    	/*
    	$this->mediawiki->returns('getProp',
    	    ['query' => ['pages' =>  [
    	        '305' => ['title' =>'Category:Pages with DOIs inactive as of 2013', 'categoryinfo' => ['pages' => 2]]
    	    ]]],
    	    ['categoryinfo', ['generator' => 'allpages', 'gapprefix' => 'Pages with DOIs inactive as of ', 'gapnamespace' => 14, 'gaplimit' => 'max']]
    	    );

    	$this->mediawiki->returns('getList',
    	    ['query' => ['categorymembers' =>  [
    	        ['title' =>'Earth', 'ns' => 0],
    	        ['title' =>'Read\'s Cavern', 'ns' => 0]
    	    ]]],
    	    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Pages with DOIs inactive as of 2013', 'cmlimit' => 'max', 'cmtype' => 'page']]
    	    );
*/

    	// Dummys

    	$this->mediawiki->returns('getList',
    	    ['query' => ['categorymembers' =>  []]]
    	    );

    	$this->mediawiki->returns('getProp',
    	    ['query' => ['pages' =>  []]]
    	    );
    }

    public function getMediawiki()
    {
        return $this->mediawiki;
    }
}