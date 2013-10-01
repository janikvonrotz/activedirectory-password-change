module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		
		watch:{
			files: ['README.md'],
			tasks: ['markdown']
		},
		
		markdown:{
			all:{
				files:[
					{
						expand: true,
						src: 'README.md',
						dest: '',
						ext: '.html'
					}
				]
			}
		}
	});

	grunt.loadNpmTasks('chains-markdown');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.registerTask('default', ['watch','markdown']);

};