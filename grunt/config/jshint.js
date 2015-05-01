// https://github.com/gruntjs/grunt-contrib-jshint
module.exports = {
    plugin: {
        options: {
            jshintrc: '<%= paths.js %>.jshintrc'
        },
        src: '<%= files.js %>'
    },
    grunt: {
        options: {
            jshintrc: 'grunt/.jshintrc'
        },
        src: [
            '<%= files.grunt %>',
            '<%= files.config %>'
        ]
    }
};
