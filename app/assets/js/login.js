import { createApp, reactive, ref } from 'vue'

const $app$ = createApp({
    setup() {
        const form = reactive({
            name: '123',
            pass: '456',
        })
        const data = ref(null)

        const handleSubmit = () => {
            $.post('/api/contact', form, null, 'json').then((res) => {
                data.value = res.data
            })
        }

        return {
            form,
            data,
            handleSubmit,
        }
    },
}).mount('#app')

window.$app$ = $app$
