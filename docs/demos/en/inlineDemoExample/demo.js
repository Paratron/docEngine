(function (){
    'use strict';

    var btn;

    btn = document.getElementById('btn-clickme');

    btn.addEventListener('click', function(){
       alert('You clicked me!');
    });
})();