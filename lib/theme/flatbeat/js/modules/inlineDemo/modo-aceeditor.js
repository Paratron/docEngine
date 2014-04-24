/**
 * modo-AceEditor
 * ==============
 * The famous ACE editor from http://ace.c9.io/
 */
(function (){
    'use strict';

    var modoCore;

    //commonJS and AMD modularization - try to reach the core.
    if(typeof modo !== 'undefined'){
        modoCore = modo;
    } else
        {if(typeof require === 'function'){
            modoCore = require('modo');
        }
}

    function cn(index, prefixed){
        if(prefixed !== undefined){
            return modoCore.AceEditor.classNames[index];
        }
        return modoCore.cssPrefix + modoCore.AceEditor.classNames[index];
    }

    modoCore.defineElement('AceEditor', ['aceEditor'], function (params){
        params = params || {};

        modoCore.Element.call(this, params);

        this.addClass(cn(0, true));

        this.el.css('min-height', 300);

        if(window.ace === undefined){
            throw new Error('Please load the Ace Editor sources first.');
        }

        this.editor = ace.edit(this.el[0]);
        this.editor.setTheme('ace/theme/' + (params.theme || 'chrome'));
        this.editor.getSession().setMode('ace/mode/' + (params.mode || 'html'));
        this.editor.getSession().setUseWrapMode(true);
        this.editor.setShowPrintMargin(false);

        this.on('show', function(){
            this.editor.resize(true);
        });
    })
        .inheritPrototype('Element')
        .extendPrototype({
            setMode: function(mode){
                this.editor.getSession().setMode('ace/mode/' + mode);
            },
            get: function(){
                return this.editor.getValue();
            },

            set: function(value){
                this.editor.setValue(value);
                this.editor.navigateFileStart();
                return this;
            },
            resize: function(){
                this.editor.resize();
            }
        });

    if(typeof exports !== 'undefined'){
        //commonJS modularization
        exports = modoCore.AceEditor;
    } else
        {if(typeof define === 'function'){
            //AMD modularization
            define('AceEditor', [], function (){
                return modoCore.AceEditor;
            });
        }
}
})();