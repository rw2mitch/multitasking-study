<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title></title>
  <meta name="author" content="">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/style.css" rel="stylesheet">
  <script src="plugins/jspsych.js"></script>
  <script src="plugins/wordfind.js"></script>
  <script src="plugins/utility.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
  
    <script>

    var form = "<form id='sendtoPHP' method='post' action='server-side.php'>"+
        "<input type='hidden' name='put-studyid-here' id='put-studyid-here' value='' />"+
        "<input type='hidden' name='put-sscode-here' id='put-sscode-here' value='' />"+
        "<input type='hidden' name='put-data-here' id='put-data-here' value='' />"+
        "</form>";

    document.head.innerHTML += form

    var ss_code = getRandomString(8, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    
    const anagram_length = 10;
    const numProbes = 4;
    var num_buttons = 0;
    var ws_found = [];
    var ws_found_num = 0;
    var anagram_found = [];
    var anagram_found_num = 0;
    var anInputCount = 0;
    var probeCount = 0;
    var finishPuzzle = false;
    var fullscreen = false
    var word_placement_data = '{ "word_placement" : [{';
    var d = new Date();
    var refreshCount = 0;

    // add a cookie to track condition
    var value_or_null = (document.cookie.match(/^(?:.*;)?\s*EC5Condition\s*=\s*([^;]+)(?:.*)?$/)||[,null])[1]
    var refreshValue_or_null = (document.cookie.match(/^(?:.*;)?\s*EC5refresh\s*=\s*([^;]+)(?:.*)?$/)||[,null])[1]

    if (value_or_null == null || typeof value_or_null == 'undefined'){
      condition = Math.ceil(Math.random()*2);
      // set expirey for 60 minutes (60 seconds * 60 minutes = 3600)
      var date = new Date();
      date.setTime(date.getTime()+(3600*1000));
      document.cookie = "EC5Condition="+condition+"; expires="+date.toGMTString()+"; path=/";
      document.cookie = "EC5refresh=0; expires="+date.toGMTString()+"; path=/";
    }
    else{
      condition = Number(value_or_null);
      var date = new Date();
      date.setTime(date.getTime()+(3600*1000));
      refreshCount = Number(refreshValue_or_null) + 1
      document.cookie = "EC5refresh="+refreshCount+"; expires="+date.toGMTString()+"; path=/";
    }

    var csvData = '"ParticipantNumber","Condition","Date/Time","refreshCount","gender","age","ws_input","bonus_ws_input","anagram_input","false_word","Probe1","Probe2","EQ1","EQ2","EQ3","EQ4","EQ5","EQ6","EQ7","EQ8","timestamp",\n' +
    '"'+ss_code+'","'+condition+'","'+d.getMonth()+'/'+d.getDate()+'/'+d.getFullYear()+'/'+d.getHours()+':'+d.getMinutes()+'","'+refreshCount+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A",\n';
    
    function showPage(doc_ele){
        doc_ele.style.visibility ='visible';
        doc_ele.style.display='inline';
    }

    function hidePage(doc_ele){
        doc_ele.style.visibility ='hidden';
        doc_ele.style.display='none';
    }

    function reverseString(str) {
    	return str.split("").reverse().join("");
    }
    
    var probe = '<div id = "thought-probe" style="font-size:24px;visibility:hidden;font-family:"Times New Roman",Times,serif;"><div class="split left" id="left" style="padding:20px;font-size:24px;font-family:"Times New Roman",Times,serif;">' +
        '<p style="font-size:24px;font-family:"Times New Roman",Times,serif;" id = "thought-probe-1"><b>How deeply were you concentrating on the viewing task?</b></p>' +
        '<p><label><input type="radio" name="rad-probe-1-answer" value="1"/>1) Not deeply at all</label></p>' +
        '<p><label><input type="radio" name="rad-probe-1-answer" value="2"/>2)</label></p>' +
        '<p><label><input type="radio" name="rad-probe-1-answer" value="3"/>3)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-1-answer" value="4"/>4)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-1-answer" value="5"/>5)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-1-answer" value="6"/>6)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-1-answer" value="7"/>7) Very deeply</label></p>'+
	'<p><i>Once you have indicated your responses, click "SAVE".</i></p>'+
        '<br></div>'+
        '<div class="split right" id="right"><p style="padding-top:20px;font-size:24px;font-family:"Times New Roman",Times,serif;" id = "thought-probe-2"><b>How much control were you exerting over your thoughts?</b></p>'+
        '<p><label><input type="radio" name="rad-probe-2-answer" value="1"/>1) No control</label></p>'+
        '<p><label><input type="radio" name="rad-probe-2-answer" value="2"/>2)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-2-answer" value="3"/>3)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-2-answer" value="4"/>4)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-2-answer" value="5"/>5)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-2-answer" value="6"/>6)</label></p>'+
        '<p><label><input type="radio" name="rad-probe-2-answer" value="7"/>7) Complete control</label></p>'+
        '<br></div>'+
      '<button id="save" type="button" class="btn letters" style="position:absolute;right:5%;bottom:8%;z-index:1;">SAVE</button></div>';

    var info_consent_letter =
      '<div id = "info_consent_letter" style="font-size:20px;"><h2>Information Letter</h2>'
      +"<p><b>Project Title:</b> Working Memory and Attention II.</p>"
      +"<p><b>Student Researcher:</b> Emilie Caron, eecaron@uwaterloo.ca, Department of Psychology, University of Waterloo</p>"
      +"<p><b>Student Researcher:</b> Allison Drody, acdrody@uwaterloo.ca, Department of Psychology, University of Waterloo</p>"
      +"<p><b>Faculty Researcher:</b> Dr. Daniel Smilek, dsmilek@uwaterloo.ca, 519-888-4567 x 36365, Department of Psychology, University of Waterloo</p>"
      +"<p><b>Faculty Researcher:</b> Dr. Jonathan Carriere, jonathan.carriere@ubishops.ca, Department of Psychology, Bishop's University</p>"
      +"<p><b>Collaborator:</b> Dr. Brandon Ralph, bcwralph@uwaterloo.ca, Department of Psychology, University of Waterloo</p>"
      +"<p>You are being invited to participate in a research study investigating working memory and attention.</p>"
      +"<p>As a participant in this study, you will be presented with a series of stimuli involving word identifiation. Thought probes will be introduced randomly throughout the task to measure your level of attention to the task.</p>"
      +"<p>It should take you no more than 6 minutes to complete this study, and you will receive $1.00USD in appreciation for your time. Throughout the study, you will also have the opportunity to increase your final reward amount.</p>"
      +"<p>Identifying information that ties collected data to you, the participant, will not be recorded in this study. Your name will not appear in any report, publication, or presentation resulting from the study. You will be completing the study through an online system operated by the University of Waterloo. When information is transmitted or stored on the internet, privacy cannot be guaranteed. There is always a risk your responses may be intercepted by a third party (e.g., government agencies, hackers). The dataset without identifiers may be shared publicly. Your identity will be confidential</p>"
      +"<p>Data collected during this study will be retained for a minimum of 7 years on a password protected network in the Department of Psychology at the University of Waterloo in Canada, to which only authorized researchers have access. De-identified data may be transferred to another institution since one of our co-investigators holds a Faculty position at Bishop's University in Canada. De-identified data related to your participation may be submitted to an open access repository or journal (i.e., the data may be publicly available). These data will be completely de-identified/anonymized prior to submission by removing all personally identifying information (e.g., names, email addresses, and certain identifying demographic information) before submission and will be presented in aggregate form in publications. This process is integral to the research process as it allows other researchers to verify results and avoid duplicating research. Other individuals may access these data by accessing the open access repository. Although the dataset without identifiers may be shared publicly, your identity will always remain confidential.</p>"
      +"<p>Please note that there are no Direct Benefits and No Risks to participation beyond what is experienced in our daily lives. By volunteering for this study, you may learn about research in psychology in general, and research about individual differences in attention. It is important that we remind you that your participation is voluntary. You are free to withdraw from the study at any time, and you may decline to proceed with the task, without penalty, or loss of participation credit. If you would like to withdraw from the study, please notify the researcher of your decision to do so. You are also encouraged to contact the researchers at any time if have any questions about the study.</p>" 
      +"<p><i>I would like to assure you that this study has been reviewed by, and received ethics clearance through, a University of Waterloo Research Ethics Committee (ORE#40904). However, the final decision about participation is yours. If you have any comments or concerns resulting from your participant in this study, please contact the Office of Research Ethics, at 1-519-888-4567 Ext. 36005, or ore-ceo@uwaterloo.ca</i></p>"
      +"<h2>Consent Form</h2>"
      +"<p>I agree to participate in the study 'Working Memory and Attention II' being conducted by Emilie Caron, Dr. Brandon Ralph, Dr. Jonathan Carriere, and Dr. Daniel Smilek of the Department of Psychology, University of Waterloo.  I have made this decision based on the information I read in the Information-Consent Letter and have had the opportunity to receive any additional details I wanted about the study. I understand that I can withdraw this consent at any time by telling the researcher who will destroy my data upon knowledge of my withdrawal and that if I withdraw my consent, I will not be penalized nor will I lose participation credit.</p>"
      +"<p>I also understand that this project has been reviewed by and received ethics clearance through a University of Waterloo Research Ethics Committee (ORE#40904), and that I may contact the Office of Research Ethics by email (ore-ceo@uwaterloo.ca) or by phone (1-519-888-4567 x36005) if I have any concerns or comments resulting from my involvement in the study. By consenting, you are not waiving your legal rights or releasing the investigator(s) or involved institution(s) from their legal and professional responsibilities.</p>"
      +"<p><b>With full knowledge of all foregoing, I agree of my own free will, to participate in this study.</b></p></div>"
      +'<button id="yes-consent-btn" class="consent-btn" onclick="instP1()"><b>I agree to participate</b></button><button id="no-consent-btn" class="consent-btn" onclick="declineParticipate()">I do not agree to participate</button>';

    var decline_to_participate = '<div class = "centered" style="font-size:20px;"><p>You have declined to participate at this time.</p><p>If you change your mind, simply reload this webpage.</p></div>'

    function declineParticipate() {
        document.body.innerHTML = decline_to_participate
	    window.scrollTo(0, 0);
    }

    var demographic_questionnaire =    
        '<div id = "demographic_questionnaire" style="font-size:20px;"><h2>Demographics Questionnaire</h2>'
        +"<p>First, we would like to collect some simple demographic information. If you prefer not to respond, simply leave the question blank</p>"
        +"<p><b>1. Please indicate which gender you identify with:</b></p>"
        +"<label><input type='radio' name='rad-gender-answer' value='Male'/>Male</label>"
        +"<label><input type='radio' name='rad-gender-answer' value='Female'/>Female</label>"
        +"<label><input type='radio' name='rad-gender-answer' value='Other'/>Other</label>"
        
        +"<p><b>2. Please indicate your age: <span id='age-output'>Not Selected</span></b></p>"
        +"<p><i>(Click and drag the slider to indicate your response)</i></p></div>"
        +'<table><th>Age (In Years) 1</th><th><input id="age-slider" onchange="showVal(this.value)" type="range" min="1" max="100" class="not-clicked"></th>'
        +'<th>100</th></table><br>'
        +'<button id="saveDQ" type="button" class="btn letters" style="position:absolute;right:5%;bottom:8%">SAVE</button></div>'

    function showVal(val) {
        document.getElementById('age-output').innerHTML = val
    }

    function demographicQuestionnaire() {
        document.body.innerHTML = demographic_questionnaire
	    window.scrollTo(0, 0);
        
        document.getElementById('saveDQ').addEventListener('click',function(){
            var Time = performance.now()
            var gender = $("input:radio[name=rad-gender-answer]:checked").val();
            var age = document.getElementById('age-slider').value
            csvData += '"N/A","N/A","N/A","N/A","'+gender+'","'+age+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(Time-startTime)+'",\n'
            document.body.innerHTML = ''
            startPuzzle()
        })

    }

    var End_Questionnaire =    
      '<div id = "End_Questionnaire" style="font-size:20px;"><h2>End Questionnaire</h2>'
      +"<p>Finally, please respond to the item(s) below and click 'Finish' to finish the study.</p>"
      +"<p> Please select one of the following descriptions that best match your experience. <p>"
      
      +"<p><b>Do you feel you were completing:</b></p>"
      +"<label><input type='radio' name='rad-video-answer' value='A single task'/>A single cognitive task</label>"
      +"<label><input type='radio' name='rad-video-answer' value='A single task with two components'/>A single cognitive tasks with two components</label>"
      +"<label><input type='radio' name='rad-video-answer' value='Two different tasks'/>Multiple cognitive tasks</label>"
        
      +"<p><b>Indicate to what extent you felt you were multitasking</b></p>"
      +"<p><label><input type='radio' name='rad-video-2-answer' value='1'/>1) Not at all</label></p>"
      +"<p><label><input type='radio' name='rad-video-2-answer' value='2'/>2)</label></p>"
      +"<p><label><input type='radio' name='rad-video-2-answer' value='3'/>3)</label></p>"
      +"<p><label><input type='radio' name='rad-video-2-answer' value='4'/>4) Somewhat</label></p>"
      +"<p><label><input type='radio' name='rad-video-2-answer' value='5'/>5)</label></p>"
      +"<p><label><input type='radio' name='rad-video-2-answer' value='6'/>6)</label></p>"
      +"<p><label><input type='radio' name='rad-video-2-answer' value='7'/>7) Totally</label></p>"
      +"<br><p>On a scale from 1 to 7, please indicate:</p>"

    // Should these be in past or present tense?

    Questions = [
        "<p><strong>Your satisfaction with your performance.</strong></p>",
        "<p><strong>How well you believe you performed relative to others.</strong></p>",
        "<p><strong>How productive you felt.</strong></p>",
        "<p><strong>How long you considered the duration of the assignment to be.</strong></p>",
        "<p><strong>How quickly you felt time passed.</strong></p>",
        "<p><strong>How rushed you felt while working.</strong></p>"
        // "<p><strong>Indicate the extent to which you multitask often.</strong></p>",
        // "<p><strong>Indicate the extent to which multitasking helps you be more efficient.</strong></p>",
        // "<p><strong>Indicate the extent to which you make more mistakes when multitasking.</strong></p>",
        // "<p><strong>Indicate the extent to which multitasking help you get things done more quickly when you are busy.</strong></p>",
        // "<p><strong>Indicate the extent to which multitasking helps you do work at hand.</strong></p>",
        // "<p><strong>Indicate the extent to which multitasking is enjoyable.</strong></p>",
        // "<p><strong>Indicate the extent to which multitasking is stressful.</strong></p>",
    ];

    Labels = [
        {'L1': 'Very unsatisfied', 'L2': 'Neither satisfied nor unsatisfied', 'L3': 'Very satisfied'},
        {'L1': 'Very poorly', 'L2': 'Average', 'L3': 'Very well'},
        {'L1': 'Very unproductive', 'L2': 'Neither productive nor unproductive', 'L3': 'Very Productive'},
        {'L1': 'Very short', 'L2': 'Moderate length', 'L3': 'Very long'},
        {'L1': 'Very slowly', 'L2': 'Time passed normally', 'L3': 'Very fast'},
        {'L1': 'Not rushed', 'L2': 'Somewhat', 'L3': 'Very rushed'}
        // {'L1': 'Not at all', 'L2': 'Moderately often', 'L3': 'Very often'},
        // {'L1': 'Not at all helpful', 'L2': 'Somewhat helpful', 'L3': 'Very helpful'},
        // {'L1': 'Much fewer mistakes', 'L2': 'No more or less mistakes', 'L3': 'Many more mistakes'},
        // {'L1': 'Much slower', 'L2': 'Neither faster nor slower', 'L3': 'Much faster'},
        // {'L1': 'Not at all helpful', 'L2': 'Somewhat helpful', 'L3': 'Very helpful'},
        // {'L1': 'Not at all enjoyable', 'L2': 'Somewhat enjoyable', 'L3': 'Very enjoyable'},
        // {'L1': 'Not at all stressful', 'L2': 'Somewhat stressful', 'L3': 'Very stressful'}
    ];

    function createEQ(Q, qNum, L1, L2, L3) {
        End_Questionnaire += Q
        for (i=0;i<7;i++) {
            num = i+1
            if (i==0) {
                label = L1;
            }
            else if (i==3) {
                label = L2
            }
            else if (i==6) {
                label = L3;
            }
            else {
                label = '';
            }
            End_Questionnaire += "<p><label><input type='radio' name='rad-video-"+qNum+"-answer' value='"+num+"'/>"+num+") "+label+"</label></p>";
        }
        
    }

    for (j=0;j<Questions.length;j++) {
        createEQ(Questions[j], j+3, Labels[j]['L1'], Labels[j]['L2'], Labels[j]['L3']);
    }
    
    End_Questionnaire += "<br><p></p>"+'<button id="endSave" type="button" class="btn letters" style="display:block;float:right;margin-bottom:10px;">SAVE</button></div></div>';

    function endQuestionnaire() {
        finishPuzzle = true
        document.body.style.backgroundColor = '#cccccc'
        document.body.innerHTML = End_Questionnaire
	    window.scrollTo(0, 0);
        save_btn = document.getElementById('endSave')
        save_btn.addEventListener('click',function(){
            var Time = performance.now()
            var EQ1 = $("input:radio[name=rad-video-answer]:checked").val();
            var EQ2 = $("input:radio[name=rad-video-2-answer]:checked").val();
            for (i=0;i<Questions.length;i++) {
                var num = i+3;
                eval('var EQ'+num+' = $("input:radio[name=rad-video-'+num+'-answer]:checked").val();')
            }
            csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+EQ1+'","'+EQ2+'","'+EQ3+'","'+EQ4+'","'+EQ5+'","'+EQ6+'","'+EQ7+'","'+EQ8+'","'+(Time-startTime)+'",\n'
            feedback()
        })
    }
    
    var credit_info = "<div class='centered' style='font-size:20px;'><p>You are almost done!</p><p>To receive your credit, click the "+'"Finish"'+" button at the bottom of this page.</p></div>"
        +'<button id="finish" type="button" onclick="endExperiment()" class="btn letters" style="position:absolute;right:5%;bottom:8%">FINISH</button></div>'

    function creditInfo() {
        document.body.innerHTML = credit_info
	window.scrollTo(0, 0);
    }

    var Feedback_Letter =    
        '<div style="position:relative;height:100%;width:100%;"><div id = "Feedback_Letter" style="position:relative;font-size:20px;"><h2>Feedback Letter</h2>'
        +"<p><b>Project Title:</b> Working Memory and Attention.</p>"
        +"<p><b>Student Reseracher:</b> Emilie Caron, eecaron@uwaterloo.ca, Department of Psychology, University of Waterloo</p>"
 	+"<p><b>Student Researcher:</b> Allison Drody, acdrody@uwaterloo.ca, Department of Psychology, University of Waterloo</p>"
        +"<p><b>Faculty Researcher:</b> Dr. Daniel Smilek, dsmilek@uwaterloo.ca, 519-888-4567 x 36365, Department of Psychology, University of Waterloo</p>"
        +"<p><b>Faculty Researcher:</b> Dr. Jonathan Carriere, jonathan.carriere@ubishops.ca, Department of Psychology, Bishop's University</p>"
        +"<p><b>Collaborator:</b> Dr. Brandon Ralph, bcwralph@uwaterloo.ca, Department of Psychology, University of Waterloo</p>"
        +"<p><b>Thank you for participating in this study!</b></p>"
        +"<p>The purpose of this study, which is part of a larger research project, is to investigate how one's perception of multitasking or single tasking influences their performance and levels of attention on the given task. In this study, participants are asked to complete word identification tasks. The dependent variable of interest was participants' performance on the cognitive tasks and their levels of attention as indicated through their responses to the thought probes. Our goal is to determine whether perceiving an activity as multi-tasking compared to single-tasking can improve individuals' levels of performance and lead to greater engagement on the said activity.</p>"
        +"<p>Please remember that the dataset without identifiers may be shared publicly. Your identity will be confidential. Data collected during this study will be retained for a minimum of 7 years on a password protected network in the Vision and Attention research lab area in the PAS building to which only authorized researchers have access. If you are interested in learning the outcome of the study, or if you have any questions or concerns, please feel free to email any of the researchers involved in the study at any time. Eventually, the conclusions of this study will be shared with the research community through seminars, conferences, presentations, and journal articles. De-identified data may be submitted to a journal or deposited in online public repositories to support our federal granting agency's policy on open data, and will be presented in aggregate form only in publications. Results are estimated to be available by the end of December 2020.</p>"
        +"<p>If you are interested in learning more about some of the issues addressed in this project, then we recommend you read the following:</p>"
        +"<p>Srna, S., Schrift, R. Y., & Zauberman, G. (2018). The Illusion of Multitasking and Its Positive Effect on Performance. Psychological Science, 29(12), 1942-1955.</p>"
        +"<p><hr><i>As with all University of Waterloo projects involving human participants, this project was reviewed by, and has received ethics clearance through a University of Waterloo Research Ethics Committee (ORE#40904). Should you have any comments or concerns resulting from your participation in this study, please contact the Office of Research Ethics, at 1-519-888-4567 Ext. 36005, ore-ceo@uwaterloo.ca</p>"
        +'<button id="next3" type="button" onclick="creditInfo()" class="btn letters" style="display:block;float:right;">NEXT</button></div>'
        
    function feedback() {
        document.body.innerHTML = Feedback_Letter
	window.scrollTo(0, 0);
    }

      // setup modular task instructions
    if (condition == 1){
        var inst_p1 =
            '<div id = "inst_p1" style="font-size:20px;"><h2>Task Instructions</h2>'
            +"<p>In this study, you will be given an <strong><u>ASSIGNMENT</u></strong> in which you will work on one study: </p>"
            +"<p><b><em>The Perceptual-Identification Study</em></b></p>"
	        +"<div style='display:block;width:800px;margin:auto;'><img  id='puzzlePic' src='img/cond1.png' style='width:800px;height:425px;border-radius:10%;margin:auto;'></div>"
            +"<p>Please select all the letters making up as many 4 letter words as you can find in the Perceptual-Identification Study.</p>"
            +"<p>In the perceptual-identification study, you will work on a word find-scrabble game. A word find-scrabble game is a game in which you observe a matrix containing letters and need to find as many meaningful words inside the matrix. The words could appear vertically, horizontally, or diagonally, and in either straight or reversed order. Furthermore, in a word find-scrabble game, you observe a string of letters, and are asked to use the letters in the string (all or part) in any order you would like, in order to construct meaningful words. </p>"
            +"<p> Your <strong><u>assignment</u></strong> in the Perceptual-Identification Study is to find (in the matrix and string of letters) as many meaningful words as possible and write these words in the appropriate box below.</p>"
            +"<p> Each correct word that you find in the perceptual-identification study will earn you additional <strong>$0.01</strong>.</p>"
            +"<p>Important!</p>"
            +"<br><p>In order for the words you identify in the word find-scrabble game to be accepted as a correct answer you may list them in any order you need to:</p>"
            +"<p>1.    Click the letters to make a word and press 'Submit'.</p>"
            +"<p>2.    Each word must be at least 4 letters long.</p>"
            +"<br><p>For example:</p>"
            +"<br><p>Window</p>"
            +"<p>Tissue</p>"
            +"<p>Term</p>"
            +"<p>Clock</p>"
            +"<p>Metro</p>"
            +"<p>.</p>"
            +"<p>.</p>"
            +"<p>.</p>"
            +"<p>One last important thing:</p>"
            +"<br><p>The task will automatically self-terminate after a certain time period, so do your best to find as many words as you can during that time.</p>"
            +'<button id="next1" type="button" onclick="instP2()"class="btn letters" style="display:block !important;float:right !important;">NEXT</button></div>'
	    +'<br><br><br>'        }
        else{
        var inst_p1 =
        '<div id = "inst_p1" style="font-size:20px;"><h2>Task Instructions</h2>'
            +"<p>In this study, you will be given a <strong><u>MULTITASKING ASSIGNMENT</u></strong> in which you will be working on two different studies: </p>"
            +"<p><b><em>The Perceptual Study and the Identification Study</em></p></b>"
	        +"<div style='display:block;width:337px;margin:auto;'><img src='img/wordsearch2.png' style='width:337px;height:382px;border-radius:10%;margin:auto;'></div>"
            +"<p>Please select all the letters making up as many 4 letter words as you can find in the Perceptual Study and 4 or more letter-long words you can construct in the Identification study.</p>"
            +"<p>In the Perceptual Study, you will work on a word find puzzle. A word find puzzle is a game in which you observe a matrix containing letters and need to find as many meaningful words inside the matrix. The words could appear vertically, horizontally, or diagonally,  and in either straight or reversed order.</p>"
            +"<div style='display:block;width:562px;margin:auto;'><img src='img/anagram2.png' style='border-radius:10%;margin:auto;'></div>"
	        +"<p> In the Identification Study, you will work on a scrabble game. A scrabble game is a game in which you observe a string of letters, and are asked to use the letters in the string (all or part) in any order you would like, in order to construct meaningful words. </p>"
            +"<p> Your <strong><u>multitasking assignment</u></strong> is to (i) in the Perceptual Study, find (in the matrix) as many meaningful words as possible and write these words in the appropriate box below, and (ii) in the Identification Study, construct (from the string of letters) as many meaningful words as possible and write these words in the appropriate box below.</p><p>In order to complete this assignment, you are to <strong><u>MULTITASK</u></strong> between the two studies </p>"
            +"<p> Each correct word that you find in the perceptual study will earn you additional <strong>$0.01</strong>.</p>"
            +"<p> Each correct word that you find in the identification study will also earn you additional <strong>$0.01</strong>.</p>" 
            +"<br><p>Important!</p>"
            +"<br><p>In order for the w`1   qwasxords you identify in the word find-scrabble game to be accepted as a correct answer you may list them in any order you need to:</p>"
            +"<p>1.    Click the letters to make a word and press 'Submit'.</p>"
            +"<p>2.    Each word must be at least 4 letters long.</p>"
            +"<br><p>For example:</p>"
            +"<br><p>Window</p>"
            +"<p>Tissue</p>"
            +"<p>Term</p>"
            +"<p>Clock</p>"
            +"<p>Metro</p>"
            +"<p>.</p>"
            +"<p>.</p>"
            +"<p>.</p>"
            +"<p>One last important thing:</p>"
            +"<br><p>The task will automatically self-terminate after a certain time period, so do your best to find as many words as you can during that time.</p>"
            +'<button id="next1" type="button" onclick="instP2()" class="btn letters" style="display:block !important;float:right !important;">NEXT</button></div>'
	    +'<br><br><br>'
        }
    
        function instP1() {
            document.body.innerHTML = inst_p1
	    window.scrollTo(0, 0);
        }

        function instP2() {
            document.body.innerHTML = inst_p2
	    window.scrollTo(0, 0);
        }
    
    var inst_p2 =
        '<div id = "inst_p2" style="font-size:20px;"><h2>Task Instructions</h2>'
        +"<p>Every once in a while, you will be interrupted and you asked to respond to the following thought-sampling probes.</p>"
        +"<p>Once you have familiarized yourself with the thought-sampling probes below, please click the 'Next' button to proceed to the next page of the instructions</p>"
	+"<div style='padding-top:40px;display:block;width:800px;margin:auto;'><img src='img/probePic.png' style='width:800px;height:425px;border-radius:10%;margin:auto;'></div>"
        +'<button id="next2" type="button" onclick="demographicQuestionnaire()" class="btn letters" style="position:absolute;right:5%;bottom:8%">NEXT</button></div>';

    function showProbe() {
        probeCount++
	if (probeCount < numProbes) {setTimeout(showProbe, 60000)};
        hidePage(document.getElementById('game'))
        hidePage(anagram)
        showPage(probe_object)
	window.scrollTo(0, 0);

        save_resp_btn = document.getElementById('save')

        save_resp_btn.addEventListener('click',function(){
            var Time = performance.now()
            probe_1_resp = $("input:radio[name=rad-probe-1-answer]:checked").val();
            probe_2_resp = $("input:radio[name=rad-probe-2-answer]:checked").val();
            csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+probe_1_resp+'","'+probe_2_resp+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(Time-startTime)+'",\n'
            if( $('input[name=rad-probe-1-answer]:checked').length>0 && $('input[name=rad-probe-2-answer]:checked').length>0){
                hidePage(probe_object)
		if (probeCount >= numProbes) {
			endQuestionnaire();
		}
		else {
			showPage(document.getElementById('game'))
                	showPage(anagram)
		}
                resetAllSliders();
            }
        })
    }

    // reset default slider values
    function resetAllSliders(){
    var inputs = document.getElementsByTagName('input');

        // reset actual value
        for (var i = 0; i < inputs.length; i++){
            if(inputs.type=="range"){inputs[i].value = '';}

            if(inputs[i].classList.contains('clicked')){
                inputs[i].classList.remove('clicked');
                inputs[i].classList.add('not-clicked');
            }

            if(inputs[i].checked){
                inputs[i].checked = false;
            }
        }
    }

    // make fullscreen and go to next trial
    function fullscreen1() {
        var element = document.documentElement;
        if (element.requestFullscreen) {
        element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
        } else if (element.webkitRequestFullscreen) {
        element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) {
        element.msRequestFullscreen();
        }
        
	document.body.innerHTML = '<div class="centered" style="font-size:25px;">Please be advised: Refreshing your browser or exiting full-screen mode will cause the task to reset. Please do not refresh your browser.</div>'
	+'<button id="firstNext" type="button" onclick="infoConsent()" class="btn letters" style="position:absolute;right:5%;bottom:8%">NEXT</button></div>';
	window.scrollTo(0, 0);

        startTimer();
        createAnswerObject();
        // setTimeout(showProbe, 60000)
        // document.body.innerHTML = probe
        // probe_object = document.getElementById('thought-probe')
    }
    
    function infoConsent() {
        document.body.innerHTML = info_consent_letter
	window.scrollTo(0, 0);
        showPage(document.getElementById('info_consent_letter'))
    }

    // set timer to 5 minutes
    function startTimer() {
        startTime = performance.now()
    }
    
    function startExperiment() {
	var regex = /.*EC5='completed'.*/
	if (regex.test(document.cookie)) {
		document.body.innerHTML = '<div class="centered" style="font-size:20px;"><p>You have already completed the experiment. Thank you for your participation.</p></div>';
	}
	else {
		document.body.innerHTML = '<div class="centered" style="font-size:20px;"><p>The experiment will switch to full screen mode when you press the button below</p><button id="jspsych-fullscreen-btn" class="jspsych-btn" onclick="fullscreen1()">Continue</button></div>';
		window.scrollTo(0, 0);
	}    
    };

    function endExperiment() {
        var exitTime = performance.now()
        csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A",'+(exitTime-startTime)+'",\n'
        document.body.innerHTML = '<div class="centered" style="font-size:20px;">This is the end of the experiment. Please wait while you are redirected.</div>';
	    window.scrollTo(0, 0);

        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.cookie = "EC5='completed'; expires="+tomorrow.toGMTString()+"; path=/";

        document.getElementById('put-studyid-here').value = 'EC5';
        document.getElementById('put-sscode-here').value = ss_code;
        document.getElementById('put-data-here').value = csvData;
        document.getElementById('sendtoPHP').submit();

    }

    function fsError() {
        location.reload()
    }
        
    // Create variable for elements on the right side of the page
    if (condition == 2) {
        var html = '<div id="myDIV" class="split right" style="background-color: #dfc1c3; border-left: 2px solid #605b6c"><div class="a" id="contents" style="position:absolute;"><h2 id="anagramText" style="margin:5px;font-family:"Times New Roman",Times,serif;font-size:24px;">Make as many words as possible using the letters below</h2><div class="a">' +
        '<div class="a">';
    }
    else {
        var html = '<div id="myDIV" class="split right" style="background-color: #cbd8eb"><div class="a" id="contents" style="position:absolute;"><h2 id="anagramText" style="margin:5px;font-family:"Times New Roman",Times,serif;font-size:24px;">Make as many words as possible using the letters below</h2><div class="a">' +
        '<div class="a">';
    }
    
    // Pick a random anagram from the list
    // all_anagrams = jsPsych.randomization.shuffle(['HOIRMNPTES', 'EMTTECSOWF', 'TPEFDORGEO']);
    // chosen_anagram = all_anagrams[0];
    randomize_anagram = 'TRBASLEHES'
    
    // Jumble the anagram (not active)
    function rand_anagram() {
        randomize_anagram = jsPsych.randomization.shuffle([chosen_anagram[0], chosen_anagram[1], chosen_anagram[2], 
        chosen_anagram[3], chosen_anagram[4], chosen_anagram[5], chosen_anagram[6], chosen_anagram[7], chosen_anagram[8], chosen_anagram[9]]);  
    }
    
    // Make a button for each letter in the anagram
    for (i = 0; i < anagram_length; i++) {
       html += '<button id="'+i+'" type="button" class="btn letters" onclick="disableButton(this.id); writeLetters(this)">'+randomize_anagram[i]+'</button>'
       num_buttons++
    }

    // Add input box and additional buttons
    html += '<p></p><div class="a"><input type="text" id="myInput" placeholder="" readonly="true"><span onclick="newElement()"' +
    'class="btnSmall letters">Submit</span><br><br><div class="a"><button type="button" class="btnSmall letters" onclick="ClearFields();">Clear</button>' +
    '</div><div class="a"><div class="b"><ul class = "ul" id="myUL"></ul></div>';
    
    // make buttons write letters to myInput
    function writeLetters(el) {
        var txt = document.getElementById("myInput");
        var number = el.innerHTML;
        txt.value = txt.value + number;
    };

    // disable button
    function disableButton(id) {
      document.getElementById(id).disabled = true;
    };

    // make a variable for each anagram button that can be used to access that button
    for (i=0; i<num_buttons; i++) {
       eval('var changeButton'+i+' = document.getElementById('+i+');')
    }
    
    // function to randomize the anagram letter order (not active)
    function randomize_button() { 
        rand_anagram()
		for (i=0; i<num_buttons; i++) {
            var button = eval('changeButton' + i)
            button.innerHTML = randomize_anagram[i]
        }
	};

    //Clear button
	function ClearFields() {
	  document.getElementById("myInput").value = "";
	  var btns = document.querySelectorAll('button');
      for (i=0; i<num_buttons; i++) {
          document.getElementById(i).disabled = false
      }
    }  

    // Show submitted words on screen
    function newElement() {
      anInputCount++

      var anTime = performance.now()
      var li = document.createElement("li");
      var inputValue = document.getElementById("myInput").value;
      var is_a_word = false
      var i=0
      var t=''

      for (num in anWordsArray) {
            if (inputValue.toUpperCase() == anWordsArray[i]) {
                is_a_word = true
                break
            }
            i++
      }
      if (inputValue.length <= 3) {
        t = document.createTextNode('Error: word < 4 digits');
        csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+inputValue+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(anTime-startTime)+'",\n'
      }
      else if (is_a_word) {
        anagram_found += inputValue + ', '
        csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+inputValue+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(anTime-startTime)+'",\n'
        anagram_found_num += 1
        t = document.createTextNode(inputValue);
      }
      else {
        t = document.createTextNode('Error: not a word')
        csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+inputValue+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(anTime-startTime)+'",\n'
      }
      li.appendChild(t);
      document.getElementById("myUL").appendChild(li);

      ClearFields()
    };
    
    
    // An array with the wordsearch words
    var words = ['rettug', 'taoc', 'ycnediserp', 'training', 'attack', 'reerac', 'roll', 'opinion', 'ylleb', 'hurt', 'sail', 'retov', 'fleet', 'resident', 'ertaeht', 'news', 'nationalist', 'kooltuo', 'customer', 'credit'];

    // code to randomly generate wordsearch if you want:

    // var puzzle = wordfind.newPuzzle(words, {
    //     height: 15,
    //     width:  15,
    //     orientations: ['horizontal', 'vertical', 'diagonal'],
    //     fillBlanks: true,
    //     preferOverlap: true
    // });
    // console.log(puzzle)


    var puzzle = [
		["o", "p", "i", "n", "i", "o", "n", "n", "j", "t", "l", "w", "s", "v", "s"],
		["s", "b", "n", "i", "a", "a", "n", "w", "a", "k", "e", "r", "g", "r", "g"],
		["e", "g", "r", "e", "t", "t", "u", "g", "g", "l", "a", "p", "m", "y", "l"],
		["y", "v", "b", "h", "w", "n", "i", "a", "i", "s", "u", "g", "e", "u", "e"],
		["c", "l", "i", "c", "u", "s", "t", "o", "m", "e", "r", "w", "n", "r", "m"],
		["n", "u", "l", "r", "a", "r", "d", "k", "n", "s", "o", "r", "b", "f", "w"],
		["e", "f", "p", "e", "r", "e", "t", "o", "v", "a", "l", "e", "i", "l", "g"],
		["d", "l", "p", "d", "b", "r", "r", "o", "l", "i", "l", "s", "p", "r", "o"],
		["i", "e", "a", "i", "h", "e", "p", "l", "s", "l", "d", "i", "y", "g", "c"],
		["s", "e", "r", "t", "a", "e", "h", "t", "c", "d", "b", "d", "s", "d", "m"],
		["e", "t", "n", "g", "t", "r", "j", "u", "u", "b", "b", "e", "y", "t", "a"],
		["r", "c", "i", "v", "u", "a", "p", "o", "w", "h", "p", "n", "a", "m", "m"],
		["p", "l", "s", "f", "n", "c", "c", "a", "j", "n", "j", "t", "a", "o", "c"],
		["o", "n", "b", "p", "f", "t", "j", "k", "r", "h", "y", "e", "d", "r", "n"],
		["t", "r", "a", "i", "n", "i", "n", "g", "o", "h", "d", "k", "w", "u", "n"]    
	];

    var anWords = 'BREATHLESS,ARBELESTS,HEARTLESS,RESTABLES,ARBELEST,BATHLESS,BEATLESS,BLASTERS,BLATHERS,BLEAREST,BLEATERS,BLETHERS,BRASHEST,BREATHES,HALBERTS,HALTERES,HARSLETS,HEATLESS,HERBLESS,LEATHERS,RESLATES,RESTABLE,RETABLES,SHELTERS,SHERBETS,SLATHERS,STABLERS,STEALERS,TEARLESS,TESSERAL,AETHERS,ARTLESS,ASHLERS,BARLESS,BASHERS,BASTERS,BATHERS,BEATERS,BEHESTS,BELTERS,BERATES,BERTHAS,BETHELS,BLAHEST,BLASTER,BLATHER,BLEATER,BLESSER,BLETHER,BRALESS,BRASHES,BREASTS,BREATHE,BREATHS,EARLESS,EASTERS,ELATERS,HALBERT,HALTERE,HALTERS,HARSLET,HASLETS,HATLESS,HEALERS,HEARSES,HEATERS,HERBALS,LABRETS,LASHERS,LASTERS,LATHERS,LEASERS,LEASHES,LEATHER,RASHEST,REALEST,REBASES,REBATES,REHEATS,RELATES,RESALES,RESEALS,RESEATS,RESLATE,RETABLE,SABLEST,SALTERS,SEALERS,SEAREST,SEATERS,SHELTAS,SHELTER,SHERBET,SLASHER,SLATERS,SLATHER,STABLER,STABLES,STEALER,STREELS,TEASELS,TEASERS,TESSERA,THALERS,TRASHES,TREBLES,TRESSEL,ABELES,ABLEST,AETHER,ALERTS,ALTERS,ARETES,ARTELS,ASHLER,ASSERT,ASTERS,BALERS,BAREST,BASEST,BASHER,BASHES,BASSER,BASSET,BASTER,BASTES,BATHER,BATHES,BEASTS,BEATER,BEHEST,BELTER,BERATE,BERETS,BERTHA,BERTHS,BESETS,BETELS,BETHEL,BLAHER,BLARES,BLASTS,BLEARS,BLEATS,BREAST,BREATH,EARTHS,EASELS,EASERS,EASTER,EATERS,ELATER,ELATES,ERASES,ESTERS,ESTRAL,ETHERS,HAERES,HALERS,HALEST,HALTER,HASLET,HASSEL,HASSLE,HASTES,HATERS,HEALER,HEARSE,HEARTS,HEATER,HERBAL,HEREAT,LABRET,LAREES,LASERS,LASHER,LASHES,LASTER,LATHER,LATHES,LEASER,LEASES,LEASTS,LESSER,LETHES,RASHES,RASSLE,RATELS,REALES,REBASE,REBATE,REBELS,REESTS,REHABS,REHEAT,RELATE,RELETS,RESALE,RESEAL,RESEAT,RESETS,RESHES,SABERS,SABLER,SABLES,SABRES,SALTER,SAREES,SEALER,SEATER,SELAHS,SEREST,SHALES,SHARES,SHEALS,SHEARS,SHEERS,SHEETS,SHELTA,SLATER,SLATES,SLEETS,STABLE,STALER,STALES,STARES,STEALS,STEELS,STEERS,STELAE,STELAR,STELES,STERES,STREEL,TABERS,TABLES,TALERS,TASERS,TASSEL,TEASEL,TEASER,TEASES,TESLAS,THALER,THEBES,THERES,THESES,THREES,TREBLE,ABELE,ABETS,ABLER,ABLES,ALERT,ALTER,ARETE,ARLES,ARSES,ARTEL,ASHES,ASSET,ASTER,BAHTS,BALER,BALES,BARES,BASER,BASES,BASTE,BASTS,BATES,BATHE,BATHS,BEALS,BEARS,BEAST,BEATS,BEERS,BEETS,BELTS,BERET,BERTH,BESES,BESET,BESTS,BETAS,BETEL,BETHS,BLAHS,BLARE,BLASE,BLAST,BLATE,BLATS,BLEAR,BLEAT,BLESS,BLEST,BLETS,BRAES,BRASH,BRASS,BRATS,BREES,EARLS,EARTH,EASEL,EASER,EASES,EASTS,EATER,ELATE,ERASE,ERSES,ESTER,ETHER,HAETS,HALER,HALES,HALTS,HARES,HARLS,HARTS,HASTE,HATER,HATES,HEALS,HEARS,HEART,HEATS,HEBES,HEELS,HERBS,HERES,HERLS,HESTS,LAREE,LARES,LASER,LASES,LASTS,LATER,LATHE,LATHS,LEARS,LEASE,LEASH,LEAST,LEERS,LEETS,LEHRS,LESES,LETHE,RALES,RASES,RATEL,RATES,RATHE,REALS,REBEL,REELS,REEST,REHAB,RELET,RESAT,RESES,RESET,RESTS,RHEAS,SABER,SABES,SABLE,SABRE,SALES,SALTS,SAREE,SATES,SEALS,SEARS,SEATS,SEELS,SEERS,SELAH,SERAL,SERES,SETAE,SETAL,SHALE,SHALT,SHARE,SHEAL,SHEAR,SHEAS,SHEER,SHEET,SLABS,SLASH,SLATE,SLATS,SLEET,STABS,STALE,STARE,STARS,STASH,STEAL,STEEL,STEER,STELA,STELE,STERE,TABER,TABES,TABLE,TAELS,TAHRS,TALER,TALES,TARES,TASER,TASES,TASSE,TEALS,TEARS,TEASE,TEELS,TELAE,TELES,TERES,TERSE,TESLA,THEBE,THERE,THESE,THREE,TRASH,TRASS,TREES,TRESS,TSARS,ABET,ABLE,ALBS,ALEE,ALES,ALTS,ARBS,ARES,ARSE,ARTS,ATES,BAHT,BALE,BALS,BARE,BARS,BASE,BASH,BASS,BAST,BATE,BATH,BATS,BEAL,BEAR,BEAT,BEER,BEES,BEET,BELS,BELT,BEST,BETA,BETH,BETS,BLAE,BLAH,BLAT,BLET,BRAE,BRAS,BRAT,BREE,EARL,EARS,EASE,EAST,EATH,EATS,EELS,ELSE,ERAS,ERST,ESES,ESSE,ESTS,ETAS,ETHS,HAES,HAET,HALE,HALT,HARE,HARL,HART,HAST,HATE,HATS,HEAL,HEAR,HEAT,HEBE,HEEL,HERB,HERE,HERL,HERS,HEST,HETS,LABS,LAHS,LARS,LASE,LASH,LASS,LAST,LATE,LATH,LATS,LEAR,LEAS,LEER,LEES,LEET,LEHR,LESS,LEST,LETS,RALE,RASE,RASH,RATE,RATH,RATS,REAL,REBS,REEL,REES,RESH,REST,RETE,RETS,RHEA,SABE,SABS,SALE,SALS,SALT,SASH,SATE,SEAL,SEAR,SEAS,SEAT,SEEL,SEER,SEES,SELS,SERA,SERE,SERS,SESH,SETA,SETS,SHAT,SHEA,SHES,SLAB,SLAT,STAB,STAR,TABS,TAEL,TAHR,TALE,TARE,TARS,TASE,TASS,TEAL,TEAR,TEAS,TEEL,TEES,TELA,TELE,TELS,THAE,THEE,TREE,TRES,TSAR,ABS,AHS,ALB,ALE,ALS,ALT,ARB,ARE,ARS,ART,ASH,ASS,ATE,BAH,BAL,BAR,BAS,BAT,BEE,BEL,BES,BET,BRA,EAR,EAT,EEL,ELS,ERA,ERE,ERS,ESS,EST,ETA,ETH,HAE,HAS,HAT,HER,HES,HET,LAB,LAH,LAR,LAS,LAT,LEA,LEE,LES,LET,RAH,RAS,RAT,REB,REE,RES,RET,SAB,SAE,SAL,SAT,SEA,SEE,SEL,SER,SET,SHA,SHE,TAB,TAE,TAR,TAS,TEA,TEE,TEL,TES,THE,AB,AE,AH,AL,AR,AS,AT,BA,BE,EH,EL,ER,ES,ET,HA,HE,LA,RE,SH,TA,TE'
    var anWordsArray = anWords.split(',');

    // variable containing functions to create wordsearch
    var WS = (function(){

        // build the markup for the rows and columns of the board
        function createBoard( num_cols, num_rows, id, unobtrusive ) {
            var i, j, id = ( id || 'gameboard' );
            if (condition == 2) {
                var html = '<div class="split left" id="left2" style="background-color: #cbd8eb; border-right: 2px solid #657286"><div id="gameContainer" class="centered"><table id="' + id + '" class="gameboard">\n';
            }
            else {
                var html = '<div class="split left" id="left2" style="background-color: #cbd8eb;"><div id="gameContainer" class="centered"><table id="' + id + '" class="gameboard">\n';
            }

            for( i = 0; i < num_rows; i++) {

                // note: using "\t" and "\n" to pretty-print the output for viewing "as code"
                html += '\t<tr>\n';  
        
                for( j = 0; j < num_cols; j++ ) {
                    html += '\t\t<td '
                         + ( unobtrusive ? '' : ''
                         +  ' onmouseover="WS.hover(this);" '
                         +  ' onmouseout ="WS.leave(this);" '
                         +  ' onclick    ="WS.click(this);" '
                         +  ' data-x    ='+j+''
                         +  ' data-y    ='+i+''
                         +  ' style    =""'
                           )
                         +  '>'
                         +  puzzle[i][j].toUpperCase()
                         +  '</td>\n'
                }
        
                html += '\t</tr>\n';


            }
        
            html += '</table>\n<h2>Word Search</h2></div></div>';

            return html;
        }

        // Alternative: less obtrusive binding of handlers to all cells
        // This is an alternative to in-lining the properties at html creation, 
        // but it needs to be triggered separately after the html is added to the DOM
        function binds( id ) {
            var el = document.getElementById( id );
            var els = el.getElementsByTagName('td');
            var i;
            for ( i in els ) {
                els[ i ].onclick = function() { WS.click(this); }
                els[ i ].onmouseover = function() { WS.hover(this); }
                els[ i ].onmouseout = function() { WS.leave(this); }
            }
        }

        // customize mouseover, mouseout, and click behavior
        // 
        // Why script these instead of just using CSS hover alone? Because we want to keep track 
        // of a third-state: clicked, which when present will negate the hover change
        //
        function hover( me ) {
            if ( me.className.match( /clicked/ ) ) return;
            if ( ! me.orgClassName ) me.orgClassName = me.className; 
            me.className = 'gameboard_over';
        }
        
        function leave( me ) {
            if ( me.className.match( /clicked/ ) ) return;
            me.className = me.orgClassName;
        }
        
        // make button translucent on click and check if a word has been found
        function click( me ) {
            var wsTime = performance.now()
	    if (me.className !== 'gameboard_clicked' && me.style.backgroundColor !== 'black') {
            	me.className = 'gameboard_clicked';
	    }
	    else if (me.style.backgroundColor !== 'black'){
	    	me.className = me.orgClassName
	    }
            var i = 0

            // For every number in the solution object (each word belongs to a number)
            for (num in word_placement_object.word_placement[0]) {
                var j = 0

                // for every letter associated with that number, find the coordinates of that letter
                for (k=0; k<(Object.keys(word_placement_object.word_placement[0][i]).length-1); k++) {
                    eval('var X = word_placement_object.word_placement[0]['+i+'].let'+j+'.X')
                    eval('var Y = word_placement_object.word_placement[0]['+i+'].let'+j+'.Y')
                    // console.log(me.getAttribute('data-x'), me.getAttribute('data-y'), X, Y)

                    // if the button clicked has the same coordinates
                    if (me.getAttribute('data-x') == X && me.getAttribute('data-y') == Y) {
                        var l = 0
                        var m = 0

                        // for every letter in the same word as that letter
                        for (k=0; k<(Object.keys(word_placement_object.word_placement[0][i]).length-1); k++) {
                            eval('var X = word_placement_object.word_placement[0]['+i+'].let'+l+'.X')
                            eval('var Y = word_placement_object.word_placement[0]['+i+'].let'+l+'.Y')
                      
                            // count if the letter has been clicked
                            if (wordsearch.rows[Y].cells[X].getAttribute('class') == 'gameboard_clicked') {
                                m++

                                // if as many buttons have been clicked as the length of the word
                                if (m == word_placement_object.word_placement[0][i].word.length && csvData.indexOf(word_placement_object.word_placement[0][i].word) == -1) {
                                    var n = 0
                                    for (k=0; k<(Object.keys(word_placement_object.word_placement[0][i]).length-1); k++) {
                                        eval('var X = word_placement_object.word_placement[0]['+i+'].let'+n+'.X')
                                        eval('var Y = word_placement_object.word_placement[0]['+i+'].let'+n+'.Y')
                                        wordsearch.rows[Y].cells[X].style.backgroundColor = 'black';
                                        wordsearch.rows[Y].cells[X].style.color = 'white';
                                        n++
                                    }

                                    // register that the word has been found
                                    ws_found += word_placement_object.word_placement[0][i].word + ", ";
                                    ws_found_num += 1;
				    
                                    if (['ycnediserp', 'reerac', 'ylleb', 'retov', 'ertaeht', 'kooltuo', 'rettug', 'taoc'].indexOf(word_placement_object.word_placement[0][i].word) >= 0) {
 					                    csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","'+reverseString(word_placement_object.word_placement[0][i].word)+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(wsTime-startTime)+'",\n'
				                    }
				                    else {
                                        if (words.indexOf(word_placement_object.word_placement[0][i].word) >= 0) {
                                    	    csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","'+word_placement_object.word_placement[0][i].word+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(wsTime-startTime)+'",\n'
                                        }
                                        else {
                                            csvData += '"N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+word_placement_object.word_placement[0][i].word+'","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","N/A","'+(wsTime-startTime)+'",\n'
                                        }
				                    }

                                    // and check if too many buttons have been pressed (total number of buttons is 225, I used 155 as the danger zone. The words take up about 105 buttons)
                                    var num_clicked = 0
                                    for( i = 0; i < 15; i++) {
                                        for( j = 0; j < 15; j++ ) {
                                            if (wordsearch.rows[i].cells[j].getAttribute('class') == 'gameboard_clicked') {
                                                num_clicked++
                                                if (num_clicked == 155) {
                                                    csvData += '"CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","CHEAT","'+(wsTime-startTime)+'",\n'
                                                    endExperiment();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            l++
                        }
                    }
                    j++
                }
                i++  
            }
        }

        // pick a random number of rows and columns to create
        // generate the markup for the game board
        // create and/or fill the "game" container with the markup
        function main( id, unobtrusive ){
            var cols = 15;
            var rows = 15;

            // find or create the "game" container on the DOM
            var el = document.getElementById('game');
            if ( ! el ) {

                // create an inpage anchor to jump to
                el = document.createElement('a');
                el.name = 'game_board';
                document.body.appendChild( el );


                // create the game board containing element (since we didn't find one already created)
                el = document.createElement('div');
                el.id = 'game';
                document.body.appendChild( el );

                // try again for the reference now that we've created it
                el = document.getElementById('game');
            } 

            // create and populate the container with our game board
            el.innerHTML = createBoard( rows, cols, id, unobtrusive );
            location.href = '#game_board';

            // if we didn't inline the props, we need to bind them after the HTML is added to the DOM
            if ( unobtrusive ) {
                WS.binds( id );
            }

        }

        // publicly accessible methods
        return {
            main  : main,
            hover : hover,
            leave : leave,
            click : click,
            binds : binds
        };

    })();

    function isFullscreen(){ return 1 >= outerHeight - innerHeight };

    function startPuzzle() {
        // create element to put the html variable in, and put the html variable in it
        document.body.innerHTML += probe 
        probe_object = document.getElementById('thought-probe')
        
        // create wordsearch
        WS.main('wordsearch', false)
        var rect = document.getElementById('wordsearch').getBoundingClientRect();
        
        anagram = document.createElement('div');
        anagram.id = 'anagram';
        
        document.body.appendChild( anagram );
        anagram.innerHTML = html
        document.getElementById('contents').style.top = ''+rect.top-10+'px'
		document.getElementById('left').style.top = ''+rect.top-10+'px'
        document.getElementById('right').style.top = ''+rect.top-10+'px'
        document.body.style.backgroundColor = 'white'
        // set end time
        // setTimeout(endQuestionnaire, 240000);

        // set fullscreen tracker
        setTimeout(function checkFS() {
            if (finishPuzzle == false) {
                if (isFullscreen() == false || document.hidden) {
                    fsError()
                }
            }
            setTimeout(checkFS, 5000);
        }, 100);

        
        // console.log(rect.top, rect.right, rect.bottom, rect.left);
        

        // set probe timer
        setTimeout(showProbe, 60000)
        // wordsearch = document.getElementById('wordsearch')
    }

    var accidental_words = [
        ["AERO",10,5,"ur"],
        ["BELL",6,13,"dl"],
        ["BELL",8,5,"ul"],
        ["BHAT",8,5,"d"],
        ["CARE",13,6,"u"],
        ["CUSTOM",5,4,"r"],
        ["DENT",10,12,"d"],
        ["DOLL",6,7,"dr"],
        ["EDIT",7,4,"d"],
        ["EGRET",3,1,"r"],
        ["FITS",13,4,"ul"],
        ["FLEE",7,2,"d"],
        ["HEAT",10,7,"l"],
        ["HEIN",4,4,"u"],
        ["LIST",7,11,"dl"],
        ["LIST",8,11,"dr"],
        ["LOOK",9,8,"u"],
        ["MOTS",5,9,"l"],
        ["NATION",1,4,"dr"],
        ["NATIONAL",1,4,"dr"],
        ["OMER",5,8,"r"],
        ["OVAL",7,8,"r"],
        ["PEAS",8,3,"ur"],
        ["PINION",1,2,"r"],
        ["PRESIDE",13,1,"u"],
        ["RAIN",15,2,"r"],
        ["RAINING",15,2,"r"],
        ["RESIDE",6,12,"d"],
        ["RESIDE",12,1,"u"],
        ["RESIDENCY",12,1,"u"],
        ["RIVE",6,4,"ul"],
        ["SARS",4,10,"ur"],
        ["SIDE",8,12,"d"],
        ["SIDE",10,1,"u"],
        ["STIFF",10,1,"dr"],
        ["TACK",11,5,"dr"],
        ["TALE",10,4,"ul"],
        ["TEEL",11,2,"u"],
        ["THEATRES",10,8,"l"],
        ["TIDE",10,4,"u"],
        ["TIFF",11,2,"dr"],
        ["TOME",5,7,"r"],
        ["TRAIN",15,1,"r"],
        ["TRIBE",11,2,"ur"],
        ["UTTER",3,7,"l"],
        ["VALE",7,9,"r"],
        ["VOTE",7,9,"l"],
        ["WAKE",2,8,"r"],
    ];

    function returnCoords(word, x, y, xmod, ymod) {
        var coord_array = []
        for (j=0;j<word.length;j++) {
            coord_array[j]= [x + j*xmod, y + j*ymod]
        }
        // console.log(coord_array)
        return coord_array
    }

    // Solves the wordsearch and created a JSON object with the coordinated of every word
    function createAnswerObject() {
        var solution = wordfind.solve(puzzle, words)
        // console.log(solution)   
        var num = 0
        for (object in solution.found) {
            var word_info = solution.found[num]
            var word_placement = '"'+num+'": { "word":"'+word_info.word+'", '
            if (word_info.orientation == 'horizontal') {
                for (i=0; i<word_info.word.length; i++) {
                    word_placement += '"let'+i+'":{"X":"'+(parseInt(word_info.x, 10) + i)+'", "Y":"'+parseInt(word_info.y, 10)+'"}, '
                }
            }
            else if (word_info.orientation == 'horizontalBack') {
                for (i=0; i<word_info.word.length; i++) {
                    word_placement += '"let'+i+'":{"X":"'+(parseInt(word_info.x, 10) - i)+'", "Y":"'+parseInt(word_info.y, 10)+'"}, '
                }
            }
            else if (word_info.orientation == 'vertical') {
                for (i=0; i<word_info.word.length; i++) {
                    word_placement += '"let'+i+'":{"X":"'+parseInt(word_info.x, 10)+'", "Y":"'+(parseInt(word_info.y, 10) + i)+'"}, '
                }
            }
            else if (word_info.orientation == 'diagonal') {
                for (i=0; i<word_info.word.length; i++) {
                    word_placement += '"let'+i+'":{"X":"'+(parseInt(word_info.x, 10) + i)+'", "Y":"'+(parseInt(word_info.y, 10) + i)+'"}, '
                }
            }
            else {
                throw new Error('Problem with identifying word placement: orientation not detected of word "' + word_info.word + '".');
            }
            word_placement = word_placement.substring(0, word_placement.length - 2);
            word_placement += ' }, '
            word_placement_data += word_placement
            num++
        }
        word_placement_data = word_placement_data.substring(0, word_placement_data.length - 2);
        word_placement_data += '}]}'
        word_placement_object = JSON.parse(word_placement_data)
        // console.log('inital object made')

        var index = 20
        for (m=0;m<accidental_words.length;m++) {
            // console.log('adding word '+accidental_words[m][0])
            var array = accidental_words[m]
            var word_object = {"word":accidental_words[m][0]}
            
            if (accidental_words[m][3] == "u") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, 0, -1)
            }
            else if (accidental_words[m][3] == "d") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, 0, 1)
            }
            else if (accidental_words[m][3] == "l") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, -1, 0)
            }
            else if (accidental_words[m][3] == "r") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, 1, 0)
            }
            else if (accidental_words[m][3] == "ul") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, -1, -1)
            }
            else if (accidental_words[m][3] == "ur") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, 1, -1)
            }
            else if (accidental_words[m][3] == "dr") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, 1, 1)
            }
            else if (accidental_words[m][3] == "dl") {
                letter_list = returnCoords(array[0], array[2]-1, array[1]-1, -1, 1)
            }
            else {
                console.log('error parsing accidental words')
            }
            // console.log(letter_list)
            for (k=0;k<letter_list.length;k++) {
                eval('word_object["let'+k+'"] = {"X":"'+letter_list[k][0]+'", "Y":"'+letter_list[k][1]+'"}')
            }
            
            word_placement_object['word_placement'][0][index] = word_object
            index++
        }
    }

    // Now we can start putting things on the screen!
    startExperiment();
    var a = 0;
    for (i=0;i<words.length;i++) {
    	a++
    }

    </script>

</body>

</html>