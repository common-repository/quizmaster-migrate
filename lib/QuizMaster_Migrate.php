<?php

class QuizMaster_Migrate {

  public $source;
  public $data;
  public $xml;
  public $quizzes = array(); // array of quiz models
  public $questions = array(); // array of question models
  public $errors = array(); // array of errors

  public function isValidImport() {

    libxml_use_internal_errors(true);

    $this->xml = simplexml_load_string( $this->data );
    $lines = explode("\n", $this->data);

    if ( $this->xml === FALSE ) {
      $errors = libxml_get_errors();

      foreach ($errors as $error) {
        $this->errors[] = $error;
      }

      libxml_clear_errors();

			return false;
    }

    return true;

  }

  public function extractQuestions( $quizXml ) {

		$questions = array();

    foreach( $quizXml->questions->question as $questionXml ) {
      $question[] = $this->extractQuestion( $questionXml );
    }

		return $question;

  }

  public function extractQuestion( $questionXml ) {

    // load the question model
    $qAtts = $questionXml->attributes();
    $answerType = (string) $qAtts['answerType'];
    $qModelName = QuizMaster_Model_QuestionMapper::questionModelByType( $answerType );
    $qModel = new $qModelName();
    $qModel->setAnswerType( $answerType );
    $qModel->setQuestion( (string) $questionXml->questionText );
    $qModel->setTitle( (string) $questionXml->title );
    $qModel->setPoints( (string) $questionXml->points );
    $qModel->setCorrectMsg( (string) $questionXml->correctMsg );
    $qModel->setIncorrectMsg( (string) $questionXml->incorrectMsg );
    $qModel->setTipMsg( (string) $questionXml->tipMsg );

    // set true/false properties
    $trueFalse = $this->getTrueFalseSettingsQuestions();
    foreach( $trueFalse as $s ) {

      if( isset( $questionXml->$s )) {
        $method = 'set' . ucfirst( $s );
        if( (string) $questionXml->$s == 'true' ) {
          $setting = true;
        } else {
          $setting = false;
        }

        $qModel->$method( $setting  );
      }
    }

    // answers
    $answers = $this->loadQuestionAnswerData( $qModel, $questionXml );
		$qModel->setAnswerData( $answers );

    // category


    return $qModel;

  }

	public function getAnswerModel( $answerType ) {

		switch( $answerType ) {
      case 'single':
        $answer = new QuizMaster_Answer_SingleChoice;
        break;
      case 'multiple':
        $answer = new QuizMaster_Answer_MultipleChoice;
        break;
      case 'free_answer':
        $answer = new QuizMaster_Answer_Free;
        break;
      case 'sort_answer':
        $answer = new QuizMaster_Answer_Sorting;
        break;
      case 'cloze_answer':
      	$answer = new QuizMaster_Answer_Fill_Blank;
        break;
    }

		return $answer;

	}

  public function loadQuestionAnswerData( $qModel, $questionXml ) {

		// store answers in array
		$answers = array();

		// load answer model
    $answerType = $qModel->getAnswerType();
    $answerModel = $this->getAnswerModel( $answerType );

		// loop through answers
		foreach( $questionXml->answers->children() as $answerXml ) {

			// get answer data
			$answerXmlAtts = $answerXml->attributes();
			$correct = (string) $answerXmlAtts->correct;
			$points = (string) $answerXmlAtts->points;
			$answerText = (string) $answerXml->answerText;
			$stortText = (string) $answerXml->stortText;

			// set answer model properties
			$answerModel->setAnswer( $answerText );
			$answerModel->setPoints( $points );
			$answerModel->setCorrect( $correct );

			// stash answer model
			$answers[] = $answerModel;

		}

		// return array of answer models
    return $answers;

  }

  public function getTrueFalseSettingsQuestions() {
    return array(
      'showPointsInBox',
      'answerPointsActivated',
      'answerPointsDiffModusActivated',
      'disableCorrect',
      'correctSameText'
    );
  }

  public function extractQuizzes() {

    foreach( $this->xml->data->quiz as $quizXml ) {
      $this->extractQuiz( $quizXml );
    }

  }

  function extractQuiz( $quizXml ) {

    $quizModel = new QuizMaster_Model_Quiz;

    // set title
    $quizModel->setName( (string) $quizXml->title );

    // set is title hidden
    $atts = $quizXml->title->attributes();
    if( $atts['titleHidden'] ) {
      $quizModel->setTitleHidden( $atts['titleHidden'][0] );
    }

    // set description
    $quizModel->setText( (string) $quizXml->text );

    // set category
    $categoryId = $this->getCategoryIdByName( (string) $quizXml->category );
    if( $categoryId ) {
      $quizModel->setCategoryName( (string) $quizXml->category );
      $quizModel->setCategoryId( $categoryId );
    }


    // set result text
    if( $quizXml->resultText ) {
      $atts = $quizXml->resultText->attributes();
      $quizModel->setResultText( (string) $quizXml->resultText );
      if( $atts['gradeEnabled'] ) {
        $quizModel->setResultGradeEnabled( $atts['gradeEnabled'][0] );
      }
    }

    // set true/false properties
    $trueFalse = $this->getTrueFalseSettings();
    foreach( $trueFalse as $s ) {
      if( isset( $quizXml->$s )) {
        $method = 'set' . ucfirst( $s );
        if( (string) $quizXml->$s == 'true' ) {
          $setting = true;
        } else {
          $setting = false;
        }
        $quizModel->$method( $setting );
      }
    }

    // quizRunOnce
    $quizRunOnce = (string) $quizXml->quizRunOnce === 'true'? true: false;
    if( $quizRunOnce ) {
      $quizModel->setQuizRunOnce( true );
      $atts = $quizXml->quizRunOnce->attributes();
      $quizRunOnceCookie = (string) $atts->cookie === 'true'? true: false;
      $quizModel->setQuizRunOnceCookie( $quizRunOnceCookie );
      $quizModel->setQuizRunOnceType( (string)$atts->type );
      $quizModel->setQuizRunOnceTime( (string)$atts->time );
    } else {
      $quizModel->setQuizRunOnce( false );
    }

    // statistics
    $quizStatistics = $quizXml->statistic;
    $atts = $quizStatistics->attributes();
    $quizStatisticsActivated = (string) $atts->activated === 'true'? true: false;
    $quizStatisticsIpLock = (string) $atts->ipLock;
    $quizModel->setStatisticsOn( $quizStatisticsActivated );
    $quizModel->setStatisticsIpLock( $quizStatisticsIpLock );

		// extract questions
		$quizModel->questions = $this->extractQuestions( $quizXml );

    // stash quiz model
    $this->quizzes[] = $quizModel;

  }

  public function getCategoryIdByName( $catName ) {
    $term = get_term_by( 'name', $catName, 'quizmaster_quiz_category');
    if( $term ) {
      return $term->term_id;
    }
    return false;
  }

  public function getTrueFalseSettings() {
    return array(
      'btnRestartQuizHidden',
      'btnViewQuestionHidden',
      'questionRandom',
      'answerRandom',
      'showPoints',
      'numberedAnswer',
      'hideAnswerMessageBox',
      'disabledAnswerMark',
      'showMaxQuestion',
      'showAverageResult',
      'prerequisite',
      'showReviewQuestion',
      'quizSummaryHide',
      'skipQuestionDisabled',
      'showCategoryScore',
      'hideResultCorrectQuestion',
      'hideResultQuizTime',
      'hideResultPoints',
      'autostart',
      'forcingQuestionSolve',
      'hideQuestionPositionOverview',
      'hideQuestionNumbering',
      'sortCategories',
      'showCategory',
      'startOnlyRegisteredUser',
    );
  }

  public function setTrueFalseSetting( $setting ) {

  }

  public function hasErrors() {
    if( !empty( $this->errors )) {
      return true;
    }
    return false;
  }

}
