# multitasking-study
This is an experiment studying the impact of the perception of multitasking on performance in a dual task involving a word search and a scrabble game. It was written in JavaScript, HTML, and CSS.

Since the experiment was made for deployment on Mechanical Turk, I included a number of preventative measures to prevent participants from abusing the financial incentives. The program detects if the participant:
<li>leaves the window to look up answers</li>
<li>refreshes or exits the page</lie>
<li>randomly guesses words too many times</lie>

Moreover, the program ensures that participants are only able to see instructions related to their assigned condition, even if they refresh the page. The data collection will not work for you, since it is customized for our server specifically. However, you can type "csvData" in the console to see what data has been collected from you as you go through the experiment.

This task is the first I have written without the crutch of the JsPsych library. It has allowed me to learn a lot about how HTML, CSS, and JavaScript work, and the most interesting challenge was making it smart enough to know when the participant finds a word in the wordsearch. Now that I know more about JavaScript development, I look forward to writing more polished and efficient code in the future.

