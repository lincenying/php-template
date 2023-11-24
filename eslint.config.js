import lincy from '@lincy/eslint-config'

const config = lincy(
    {
        overrides: {

        },
    },
    {
        ignores: [
            '**/static',
        ],
    },
)

// ;(async () => {
//     console.log(await config)
// })()

export default config
