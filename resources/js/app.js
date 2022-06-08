import Vue from "vue";
import Index from "./components/Index";
import Update from "./components/Update";

require('./bootstrap');

new Vue({
    el: '#app',

    components: {
        Index,
        Update
    }
});
