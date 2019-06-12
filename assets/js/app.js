/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
// const $ = require('jquery');

require('amplitudejs');
// require('popper.js');
// require('bootstrap');

import 'bootstrap';  // js-file
import 'bootstrap/dist/css/bootstrap.css'; // css file

require('foundation');

// import 'foundation.css'; // css file

// import Popper from 'popper.js';
// import $ from 'jquery';
const $ = require('jquery');
const Popper = require('popper.js');


require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');


require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
