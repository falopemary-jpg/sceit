<?php
// interview-rubric.php

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $candidateName = trim($_POST['candidateName'] ?? '');
    $interviewerName = trim($_POST['interviewerName'] ?? '');
    $interviewerRank = trim($_POST['interviewerRank'] ?? '');
    $ratings = $_POST['ratings'] ?? [];
    
    if ($candidateName && $interviewerName && $interviewerRank && count($ratings) === 6) {
        $totalScore = array_sum($ratings);
        $timestamp = date('Y-m-d H:i:s');
        
        // Prepare data line for text file
        $line = sprintf(
            "[%s] Candidate: %s | Interviewer: %s (%s) | Scores: %s | Total: %d/30\n",
            $timestamp,
            $candidateName,
            $interviewerName,
            $interviewerRank,
            implode(', ', $ratings),
            $totalScore
        );
        
        // Append to assessments.txt
        file_put_contents('assessments.txt', $line, FILE_APPEND | LOCK_EX);
        
        $message = "Assessment submitted successfully!";
        $messageType = "success";
    } else {
        $message = "Please complete all fields and ratings.";
        $messageType = "error";
    }
}

// Load all assessments
function loadAssessments() {
    if (!file_exists('assessments.txt')) {
        return [];
    }
    
    $lines = file('assessments.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $assessments = [];
    
    foreach ($lines as $line) {
        // Parse the line
        if (preg_match('/\[(.*?)\] Candidate: (.*?) \| Interviewer: (.*?) \((.*?)\) \| Scores: (.*?) \| Total: (\d+)\/30/', $line, $matches)) {
            $assessments[] = [
                'timestamp' => $matches[1],
                'candidateName' => $matches[2],
                'interviewerName' => $matches[3],
                'interviewerRank' => $matches[4],
                'scores' => $matches[5],
                'totalScore' => intval($matches[6])
            ];
        }
    }
    
    return $assessments;
}

$assessments = loadAssessments();

// Group by candidate
$candidateGroups = [];
foreach ($assessments as $assessment) {
    $candidateGroups[$assessment['candidateName']][] = $assessment;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCEIT Tutors Interview Rubric</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.95;
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .input-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .input-group {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .input-group input, .input-group select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
            background: white;
        }

        .input-group input:focus, .input-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .criteria-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .criteria-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .criteria-header {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .rating-options {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .rating-label-container {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .rating-label-container:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .rating-label-container input[type="radio"] {
            display: none;
        }

        .rating-label-container input[type="radio"]:checked + .rating-content {
            color: white;
        }

        .rating-label-container:has(input[type="radio"]:checked) {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .rating-content .rating-number {
            font-size: 24px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .rating-content .rating-text {
            font-size: 12px;
            display: block;
        }

        .total-score {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin-top: 30px;
        }

        .total-score h3 {
            font-size: 18px;
            margin-bottom: 10px;
            opacity: 0.95;
        }

        .total-score .score {
            font-size: 48px;
            font-weight: bold;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .results-section {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #e0e0e0;
        }

        .results-header {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .candidate-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .candidate-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .interviewer-ratings {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .interviewer-score {
            background: white;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .average-score {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .input-group {
                grid-template-columns: 1fr;
            }

            .rating-options {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SCEIT Tutors Interview Assessment</h1>
            <p>Rate candidates across key criteria • Multi-interviewer support</p>
        </div>

        <div class="content">
            <?php if (isset($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="assessmentForm">
                <input type="hidden" name="action" value="submit">
                
                <div class="input-section">
                    <div class="input-group">
                        <input type="text" name="candidateName" placeholder="Candidate Name *" required>
                        <input type="text" name="interviewerName" placeholder="Your Name (Interviewer) *" required>
                        <select name="interviewerRank" required>
                            <option value="">Select Your Rank *</option>
                            <option value="Junior Interviewer">Junior Interviewer</option>
                            <option value="Senior Interviewer">Senior Interviewer</option>
                            <option value="Lead Interviewer">Lead Interviewer</option>
                            <option value="Manager">Manager</option>
                            <option value="Director">Director</option>
                            <option value="HR Personnel">HR Personnel</option>
                            <option value="Department Head">Department Head</option>
                        </select>
                    </div>
                </div>

                <?php
                $criteria = [
                    [
                        'name' => 'Knowledge of Subject Matter',
                        'descriptions' => ['Very limited understanding', 'Basic knowledge with gaps', 'Average understanding', 'Strong knowledge', 'Excellent mastery']
                    ],
                    [
                        'name' => 'Experience',
                        'descriptions' => ['No relevant experience', 'Minimal experience', 'Moderate experience', 'Good relevant experience', 'Extensive experience']
                    ],
                    [
                        'name' => 'Soft Skills',
                        'subtitle' => '(communication, confidence, attitude)',
                        'descriptions' => ['Very weak', 'Below average', 'Average', 'Good', 'Excellent']
                    ],
                    [
                        'name' => 'Physical Appearance',
                        'subtitle' => '(neatness & professionalism)',
                        'descriptions' => ['Very poor', 'Poor', 'Fair', 'Good', 'Very professional']
                    ],
                    [
                        'name' => 'Industry Awareness',
                        'descriptions' => ['No awareness', 'Limited awareness', 'Fair awareness', 'Good awareness', 'Excellent awareness']
                    ],
                    [
                        'name' => 'Knowledge of SCEIT',
                        'descriptions' => ['No knowledge', 'Very limited', 'Basic knowledge', 'Good understanding', 'Excellent understanding']
                    ]
                ];

                foreach ($criteria as $index => $criterion):
                ?>
                    <div class="criteria-card">
                        <div class="criteria-header">
                            <?php echo htmlspecialchars($criterion['name']); ?>
                            <?php if (isset($criterion['subtitle'])): ?>
                                <span style="font-weight: 400; color: #666;"><?php echo htmlspecialchars($criterion['subtitle']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="rating-options">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="rating-label-container">
                                    <input type="radio" name="ratings[<?php echo $index; ?>]" value="<?php echo $i; ?>" required>
                                    <div class="rating-content">
                                        <span class="rating-number"><?php echo $i; ?></span>
                                        <span class="rating-text"><?php echo htmlspecialchars($criterion['descriptions'][$i-1]); ?></span>
                                    </div>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="total-score">
                    <h3>Total Score</h3>
                    <div class="score"><span id="totalScore">0</span>/30</div>
                </div>

                <button type="submit" class="submit-btn">Submit Assessment</button>
            </form>

            <div class="results-section">
                <h2 class="results-header">All Assessments</h2>
                <div id="resultsContainer">
                    <?php if (empty($candidateGroups)): ?>
                        <p style="color: #666;">No assessments yet. Be the first to submit one!</p>
                    <?php else: ?>
                        <?php foreach ($candidateGroups as $candidateName => $group): ?>
                            <?php
                            $avgScore = array_sum(array_column($group, 'totalScore')) / count($group);
                            ?>
                            <div class="candidate-card">
                                <div class="candidate-name"><?php echo htmlspecialchars($candidateName); ?></div>
                                <div class="average-score">Average: <?php echo number_format($avgScore, 1); ?>/30</div>
                                <div class="interviewer-ratings">
                                    <?php foreach ($group as $assessment): ?>
                                        <div class="interviewer-score">
                                            <strong><?php echo htmlspecialchars($assessment['interviewerName']); ?></strong>
                                            <br>
                                            <em style="color: #666;"><?php echo htmlspecialchars($assessment['interviewerRank']); ?></em>
                                            <br>
                                            Score: <?php echo $assessment['totalScore']; ?>/30
                                            <br>
                                            <small style="color: #999;"><?php echo $assessment['timestamp']; ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update total score as user selects ratings
        const form = document.getElementById('assessmentForm');
        const radios = form.querySelectorAll('input[type="radio"]');
        
        radios.forEach(radio => {
            radio.addEventListener('change', updateTotal);
        });

        function updateTotal() {
            let total = 0;
            const checkedRadios = form.querySelectorAll('input[type="radio"]:checked');
            checkedRadios.forEach(radio => {
                total += parseInt(radio.value);
            });
            document.getElementById('totalScore').textContent = total;
        }
    </script>
</body>
</html>