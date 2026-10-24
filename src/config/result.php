<?php

return [
    // printed under the department name on every downloaded result sheet
    'university' => env('RESULT_UNIVERSITY', 'DEVI AHILYA VISHWAVIDYALAYA, INDORE'),

    // the three signature lines at the bottom of the sheet
    'signatures' => ['Prepared by', 'Coordinator', 'Head'],

    // Equivalent Percentage = CGPA x this number
    'cgpa_multiplier' => 10,
];