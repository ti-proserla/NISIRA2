import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

var routes =[
    { 
        path: '', 
        component: require('./Home.vue').default,
    },
    { 
        path: '/boletas', 
        component: require('./Boletas.vue').default,
    },
    // {
    //     path: '/*',
    //     component: require('./view/404.vue').default
    // }
];




var router=new VueRouter({
    mode: 'history',
    routes,
    linkExactActiveClass: "active",
    scrollBehavior (to, from, savedPosition) {
        return { x: 0, y: 0 }
    }
});
export default router;