/**
* The purpose of the Router (vue-router plugin of vuejs) is to handle change of url and
* attach corresponding pages to the <router-view></router-view> component (in App.vue component)
* Vue-router documentation https://router.vuejs.org/
*/
import Vue from 'vue'
import VueRouter from 'vue-router'
import PageListUsers from 'components/pages/PageListUsers.vue'
import PageListDocuments from 'components/pages/PageListDocuments.vue'
import PageNews from 'components/pages/PageNews.vue'
import PageFTEbay from 'components/pages/PageFTEbay.vue'
import PageUserDetails from 'components/pages/PageUserDetails.vue'
import PageLogin from 'components/pages/PageLogin.vue'
import Store from './Store.js'

Vue.use(VueRouter)

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            component: PageNews,
            name: 'page-news',
            meta: {
                title: 'News'
            }
        },
        {
            path: '/documents/:collectionSlug',
            component: PageListDocuments,
            name: 'list-documents',
            props: true,
            meta: {
                title: 'Documents'
            }
        },
        {
            path: '/ftebay',
            component: PageFTEbay,
            name: 'page-ftebay',
            meta: {
                title: 'FTEbay'
            }
        },
        {
            path: '/users',
            component: PageListUsers,
            name: 'page-list-users',
            meta: {
                title: 'Person Finder'
            }
        },
        {
            path: '/user/:userId',
            component: PageUserDetails,
            name: 'page-user-details',
            props: true
        },
        {
            path: '/login',
            component: PageLogin,
            name: 'login'
        },
        {
            path: '/profile',
            component: PageListUsers,
            name: 'profile'
        },
        {
            path: '/settings',
            component: PageListUsers,
            name: 'settings'
        },
        // Fallback if no route matches, we redirect to the homepage
        {
            path: '*',
            redirect: '/'
        }
    ]
})

/**
* Custom method added to the vue-router instance for handling navigation for :
* 1) Link to be opened in a Webview (iframe)
* 2) Link to be opened in the device browser (outside of the app)
* 3) Standard vue-router routes
*
* @param link Object {url: vueRouterPath|hyperlink, webview: true|false}
*/

/*
* Before each route change this function is triggered so we can apply some custom behavior
*/
// router.beforeEach(function (to, from, next) {
//     // Add route class to the body
//     document.body.className = to.name
//
//     /*
//         Navigation guard:
//         1) Redirects the user to the login page if not logged-in
//         2) Redirects the user to the quizz page if a Quizz is in progress (Store.state.forceQuizzDisplay !== null)
//     */
//     console.log('change route to', to)
//     console.log('change route from', from)
//     console.log('Store.state.authToken', Store.state.authToken)
//
//     if (to.name !== 'login' && (!Store.state.authToken || Store.state.authToken === 'null')) {
//         next({ name: 'login' })
//     }
//     else {
//         next()
//     }
// })

/*
* After each route change this function is triggered so we can apply some custom behavior
*/
router.afterEach(function (to, from) {
    // Scroll up to the top of the page
    window.scrollTo(0, 0)
})

export default router
