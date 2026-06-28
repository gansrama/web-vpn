import type { App } from 'vue'

import { createVuetify } from 'vuetify'
import defaults from './defaults'
import { icons } from './icons'
import { themes } from './theme'

// Styles
import 'vuetify/styles'

export default function (app: App) {
  const vuetify = createVuetify({
    defaults,
    icons,
    theme: {
      defaultTheme: 'light',
      themes,
    },
    ssr: false,
  })

  app.use(vuetify)
}
