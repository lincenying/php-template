/* global Vue */

const $app$ = new Vue({
    el: '#app',
    name: 'app',
    components: {},
    directives: {},
    data: {
        form: {
            name: '',
            email: '',
            message: '',
        },
        data: null,
    },
    computed: {},
    watch: {},
    mounted() {},
    methods: {
        handleSubmit() {
            console.log(this.form)
            $.post('/api/contact', this.form, null, 'json').then((data) => {
                console.log(data)
                this.data = data.data
            })
        },
    },
})
window.$app$ = $app$
