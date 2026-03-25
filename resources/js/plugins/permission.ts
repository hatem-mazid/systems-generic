import { useUserStore } from '../stores/user'

export default {
  install: (app) => {
    // Create the global $hasPermission method
    app.config.globalProperties.$hasPermission = (permission) => {
      const userStore = useUserStore()
      return userStore.hasPermission(permission)
    }

    // Create a composable for setup script usage
    app.provide('hasPermission', (permission) => {
      const userStore = useUserStore()
      return userStore.hasPermission(permission)
    })
  }
}
