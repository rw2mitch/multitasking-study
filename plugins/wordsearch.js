var WS = (function(){

    // return a random character from the list in data
    function getRandomLetter() {
        var data  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var rnd   = parseInt(Math.random()*data.length);
        
        return data.charAt( rnd );
    }

    // build the markup for the rows and columns of the board
    function createBoard( num_cols, num_rows, id, unobtrusive ) {
        var i, j, id = ( id || 'gameboard' );
        var html = '<table id="' + id + '" class="gameboard">\n';

        for( i = 0; i < num_rows; i++) {

            // note: using "\t" and "\n" to pretty-print the output for viewing "as code"
            html += '\t<tr>\n';  
    
            for( j = 0; j < num_cols; j++ ) {
                html += '\t\t<td '
                     + ( unobtrusive ? '' : ''
                     +  ' onmouseover="WS.hover(this);" '
                     +  ' onmouseout ="WS.leave(this);" '
                     +  ' onclick    ="WS.click(this);" '
                       )
                     +  '>'
                     +  getRandomLetter() 
                     +  '</td>\n'
            }
    
            html += '\t</tr>\n';
        }
    
        html += '</table>\n'

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
    
    function click( me ) {
        me.className = 'gameboard_clicked';
    }

    // pick a random number of rows and columns to create
    // generate the markup for the game board
    // create and/or fill the "game" container with the markup
    function main( id, unobtrusive ){
        var cols = 7 + parseInt( Math.random() * 5 );
        var rows = 7 + parseInt( Math.random() * 5 );

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