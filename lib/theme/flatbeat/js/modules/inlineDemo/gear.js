(function (){
    'use strict';

    var ui,
        demoFrame,
        aceModes,
        height;

    aceModes = {
        js: 'javascript'
    };

    ui = modo.generate([
        {
            type: 'Container',
            ref: 'root',
            children: [
                {
                    type: 'ToggleGroup',
                    ref: 'toggleGroup',
                    params: {
                        elements: (function (){
                            var out = {'Demo': lang.demoTabLabel};

                            _.each(window.files, function(file){
                                out[file.replace(/(\.|\/)/g, '_')] = file;
                            });

                            return out;
                        })()
                    },
                    on: {
                        change: function(){
                            var val = this.get();

                            if(val.substr(0, 5) === 'http:' || val.substr(0, 6) === 'https:'){
                                window.open(val);
                                this.set('Demo');
                                return;
                            }

                            ui.viewstack.set(val);
                        }
                    }
                },
                {
                    type: 'ViewStack',
                    ref: 'viewstack'
                },
                {
                    type: 'Container',
                    ref: 'saveNotification',
                    params: {
                        className: 'saveNotification'
                    }
                }
            ]
        }
    ]);

    _.each(window.links, function(value, key){
        var linkButton = new modo.ToggleButton({
            label: key
        });

        linkButton.addClass('link');

        var insObj = {};

        insObj[value] = linkButton;

        ui.toggleGroup.add(insObj);
    });

    if(window.editable){
        ui.root.el.append('<div class="isEditableNotice" title="' + lang.isEditableNotice + '"></div>')
    }

    ui.saveNotification.hide().el.html('Saving changes...');

    height = window.innerHeight - 27;

    ui.viewstack.el.css({
        height: height
    });

    demoFrame = new modo.Container();
    demoFrame.el.html('<iframe src="' + window.demoTarget + '" id="demoFrame"></iframe>');
    demoFrame.el.css('height', height-3);
    demoFrame.el.children('iframe').css('height', height-3);

    ui.viewstack.add({'Demo': demoFrame});

    modo.init(ui.root);

    _.each(window.files, function (file){
        var ext = file.split('.').pop();
        var editor = new modo.AceEditor({
            mode: aceModes[ext] !== undefined ? aceModes[ext] : ext
        });
        var insertObj = {};
        var fileId = file.replace(/(\.|\/)/g, '_');
        var source;

        insertObj[fileId] = editor;

        ui.viewstack.add(insertObj);

        source = $('#file_' + fileId).html();

        source = source.replace(/\%-gts-\%/g, '<').replace(/\%-lts-\%/g, '>');

        editor.set(source);
        editor.editor.setReadOnly(!window.editable);
        editor.saveTimeout = null;
        editor.filename = file;
        if(window.editable){
            editor.editor.on('change', function(){
                clearTimeout(editor.saveTimeout);
                editor.saveTimeout = setTimeout(function(){
                    ui.saveNotification.show();
                    $.post(editor.filename, {data: editor.get()}, function(){
                        ui.saveNotification.hide();
                        demoFrame.el.find('iframe').attr('src', window.demoTarget + '?' + Math.random().toString().split('.').pop());
                    });
                }, 500);
            });
        }

        editor.el.css({
            'min-height': 0,
            height: height
        });
        editor.resize();
    });

    if(window.editable){
        var theInterval = setInterval(function(){
            $.get(window.basePath + 'en/module/demopulse/' + sandboxId, function(result){
                if(result != '1'){
                    clearInterval(theInterval);
                    ui.toggleGroup.set('Demo').hide();
                    ui.saveNotification.show().el.html('Your Sandbox session has expired. Reload this page to start a new one.');
                }
            });
        }, 30000);
    }
})();
