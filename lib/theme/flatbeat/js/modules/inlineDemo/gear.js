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
                    params: {
                        elements: (function (){
                            var out = {'Demo': 'Demo'};

                            _.each(window.files, function(file){
                                out[file.split('.').join('_')] = file;
                            });

                            return out;
                        })()
                    },
                    on: {
                        change: function(){
                            ui.vs.set(this.get());
                        }
                    }
                },
                {
                    type: 'ViewStack',
                    ref: 'vs'
                }
            ]
        }
    ]);

    height = window.innerHeight - 27;

    ui.vs.el.css({
        height: height
    });

    demoFrame = new modo.Container();
    demoFrame.el.html('<iframe src="' + window.demoTarget + '" id="demoFrame"></iframe>');

    ui.vs.add({'Demo': demoFrame});

    modo.init(ui.root);

    _.each(window.files, function (file){
        var ext = file.split('.').pop();
        var editor = new modo.AceEditor({
            mode: aceModes[ext] !== undefined ? aceModes[ext] : ext
        });
        var insertObj = {};
        var fileId = file.split('.').join('_');

        insertObj[fileId] = editor;

        ui.vs.add(insertObj);

        editor.set($('#file_' + fileId).html());
        editor.editor.setReadOnly(!window.editable);

        editor.el.css({
            'min-height': 0,
            height: height
        });
        editor.resize();
    });
})();
