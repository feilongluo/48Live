/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue');
window.axios = require('axios');

import VueRouter from 'vue-router';
import routes from './routes';
import iView from 'iview';
import 'iview/dist/styles/iview.css';


import flvjs from 'flv.js';

import VueVideoPlayer from 'vue-video-player';
require('video.js/dist/video-js.css');
require('vue-video-player/src/custom-theme.css');
import 'videojs-contrib-hls';
import 'videojs-flash';

import Barrage from "vue2-barrage";


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */


Vue.use(VueRouter);
Vue.use(iView);
Vue.use(VueVideoPlayer);
Vue.use(Barrage);
Vue.prototype.$flvjs = flvjs;

const router = new VueRouter({
    routes
});

const app = new Vue({
    router,
    el:'#app',
});

