/**
* The purpose of the Router (vue-router plugin of vuejs) is to handle change of url and
* attach corresponding pages to the <router-view></router-view> component (in App.vue component)
* Vue-router documentation https://router.vuejs.org/
*/
import Vue from 'vue'
import VueRouter from 'vue-router'
import PageListUsers from 'components/pages/PageListUsers.vue'
import PageListDocuments from 'components/pages/PageListDocuments.vue'
import PageListEditorial from 'components/pages/PageListEditorial.vue'
import PageBooking from 'components/pages/PageBooking.vue'
import BookingEvent from 'components/BookingEvent.vue'
import PageLogin from 'components/pages/PageLogin.vue'
import PageManager from 'components/pages/PageManager.vue'
import PageCustom from 'components/pages/PageCustom.vue'
import PageITReps from 'components/pages/PageITReps.vue'
import PageMyZeusCalendar from 'components/pages/PageMyZeusCalendar.vue'
import Store from './Store.js'

Vue.use(VueRouter)

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            component: PageListEditorial,
            name: 'page-news',
            meta: {
                labels: {
                    title: 'News',
                    formTitleNew: 'New Announcement',
                    formTitleEdit: 'Edit an Announcement'
                },
                api: {
                    getAll: 'editorial/news',
                    post: 'editorial/news',
                    edit: 'editorial/news/{id}/edit',
                    delete: 'editorial/news/{id}',
                    imageUpload: 'editorial-image-upload'
                },
                settings: {
                    totalToDisplay: 10
                }
            }
        },
        {
            path: '/my-zeus-calendar',
            component: PageMyZeusCalendar,
            name: 'page-my-zeus-calendar',
            meta: {
                labels: {
                    title: 'My Zeus Calendar'
                }
            }
        },
        {
            path: '/documents/:collectionSlug',
            component: PageListDocuments,
            name: 'list-documents',
            props: true,
            meta: {
                labels: {
                    title: 'Documents'
                }
            }
        },
        {
            path: '/pages/:section/:id',
            component: PageCustom,
            name: 'custom-pages',
            props: true,
            // meta: {
            //     labels: {
            //         title: '{section} Pages'
            //     }
            // }
        },

        {
            path: '/page-manager',
            component: PageManager,
            children: [
                {
                    name: 'page-sport',
                    path: 'page-sport',
                    meta: {
                        labels: {
                            title: 'Sport Page Manager',
                            formTitleNew: 'New Page',
                            formTitleEdit: 'Edit Page'
                        },
                        api: {
                            getAll: 'editorial/page-sport',
                            post: 'editorial/page-sport',
                            edit: 'editorial/page-sport/{id}/edit',
                            delete: 'editorial/page-sport/{id}',
                            imageUpload: 'editorial-image-upload'
                        },
                        settings: {

                        }
                    }
                },
                {
                    name: 'page-career',
                    path: 'page-career',
                    meta: {
                        labels: {
                            title: 'Career Page Manager',
                            formTitleNew: 'New Page',
                            formTitleEdit: 'Edit Page'
                        },
                        api: {
                            getAll: 'editorial/page-career',
                            post: 'editorial/page-career',
                            edit: 'editorial/page-career/{id}/edit',
                            delete: 'editorial/page-career/{id}',
                            imageUpload: 'editorial-image-upload'
                        },
                        settings: {

                        }
                    }
                },
                {
                    name: 'page-entertainment',
                    path: 'page-entertainment',
                    meta: {
                        labels: {
                            title: 'Entertainment Page Manager',
                            formTitleNew: 'New Page',
                            formTitleEdit: 'Edit Page'
                        },
                        api: {
                            getAll: 'editorial/page-entertainment',
                            post: 'editorial/page-entertainment',
                            edit: 'editorial/page-entertainment/{id}/edit',
                            delete: 'editorial/page-entertainment/{id}',
                            imageUpload: 'editorial-image-upload'
                        },
                        settings: {

                        }
                    }
                }
            ]
        },
        {
            path: '/ftebay',
            component: PageListEditorial,
            name: 'page-ftebay',
            meta: {
                labels: {
                    title: 'FTEbay',
                    formTitleNew: 'New FTEbay Offer',
                    formTitleEdit: 'Edit an FTEbay Offer'
                },
                api: {
                    getAll: 'editorial/ftebay',
                    post: 'editorial/ftebay',
                    edit: 'editorial/ftebay/{id}/edit',
                    delete: 'editorial/ftebay/{id}',
                    imageUpload: 'editorial-image-upload'
                },
                settings: {
                    totalToDisplay: 100
                }
            }
        },
        {
            path: '/users',
            component: PageListUsers,
            name: 'page-list-users',
            meta: {
                labels: {
                    title: 'Person Finder'
                }
            }
        },
        {
            path: '/bookings',
            component: PageBooking,
            children: [
                {
                    name: 'minivan-booking',
                    path: 'minivan',
                    component: BookingEvent,
                    meta: {
                        labels: {
                            title: 'Minivan Booking',
                        },
                        api: {
                            getAll: 'bookings/minivan',
                            post: 'bookings/minivan',
                            edit: 'bookings/minivan/{id}/edit',
                            changeState: 'bookings/minivan/{id}/changeState',
                            markAsPaid: 'bookings/minivan/{id}/markAsPaid',
                            getPriceApi: 'bookings/minivan/calculatePrice',
                            getResources: 'bookings/minivan/resources'
                        },
                        settings: {
                            minimumHours: 1,
                            maximumHours: 8,
                            multiResources: false
                        }
                    }
                },
                {
                    name: 'tv-booking',
                    path: 'tv',
                    component: BookingEvent,
                    meta: {
                        labels: {
                            title: 'TV Booking',
                        },
                        api: {
                            getAll: 'bookings/tv',
                            post: 'bookings/tv',
                            edit: 'bookings/tv/{id}/edit',
                            changeState: 'bookings/tv/{id}/changeState',
                            getResources: 'bookings/tv/resources'
                        },
                        settings: {
                            minimumHours: 0.5,
                            maximumHours: 4,
                            multiResources: false
                        }
                    }
                },
                {
                    name: 'barbecue-booking',
                    path: 'barbecue',
                    component: BookingEvent,
                    meta: {
                        labels: {
                            title: 'Barbecue Booking',
                        },
                        api: {
                            getAll: 'bookings/barbecue',
                            post: 'bookings/barbecue',
                            edit: 'bookings/barbecue/{id}/edit',
                            changeState: 'bookings/barbecue/{id}/changeState',
                            getResources: 'bookings/barbecue/resources'
                        },
                        settings: {
                            minimumHours: 0.5,
                            maximumHours: 4,
                            multiResources: true
                        }
                    }
                }
            ],
            props: true
        },
        {
            path: '/login',
            component: PageLogin,
            name: 'login'
        },
        {
            path: '/settings',
            component: PageListUsers,
            name: 'settings',
            meta: {
                labels: {
                    title: 'Settings'
                }
            }
        },
        {
            path: '/it-reps',
            component: PageITReps,
            name: 'page-it-reps'
        },
        // Fallback if no route matches, we redirect to the homepage
        {
            path: '*',
            redirect: '/'
        }
    ]
})

/*
* After each route change this function is triggered so we can apply some custom behavior
*/
router.afterEach(function (to, from) {
    // Scroll up to the top of the page
    window.scrollTo(0, 0)
})

export default router
