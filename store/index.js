export const state = () => ({
  theme: 'classic',
})

export const mutations = {
  changeTheme(state, theme) {
    state.theme = theme
  },
}
export const actions = {
  changeTheme(state, theme) {
    state.commit('changeTheme', theme)
  },
}
