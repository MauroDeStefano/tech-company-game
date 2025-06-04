import { ref, computed, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { api } from '@/services/api'

export function useUser() {
  const authStore = useAuthStore()
  
  const loading = ref(false)
  const updating = ref(false)
  const error = ref(null)
  
  const profileForm = reactive({
    name: '',
    email: '',
    current_password: '',
    password: '',
    password_confirmation: '',
  })
  
  const currentUser = computed(() => authStore.user)
  
  async function fetchProfile() {
    try {
      loading.value = true
      error.value = null
      
      const response = await api.get('/user')
      authStore.setUser(response.data.data)
      populateForm(response.data.data)
      
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Errore nel caricamento del profilo'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateProfile(formData = null) {
    try {
      updating.value = true
      error.value = null
      
      const dataToSend = formData || { ...profileForm }
      
      if (!dataToSend.password) {
        delete dataToSend.current_password
        delete dataToSend.password
        delete dataToSend.password_confirmation
      }
      
      const response = await api.put('/user', dataToSend)
      authStore.setUser(response.data.data)
      
      profileForm.current_password = ''
      profileForm.password = ''
      profileForm.password_confirmation = ''
      
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Errore nell\'aggiornamento del profilo'
      throw err
    } finally {
      updating.value = false
    }
  }

  async function deleteAccount() {
    try {
      loading.value = true
      error.value = null
      
      await api.delete('/user')
      authStore.logout()
      
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Errore nell\'eliminazione dell\'account'
      throw err
    } finally {
      loading.value = false
    }
  }

  function populateForm(userData) {
    profileForm.name = userData.name || ''
    profileForm.email = userData.email || ''
    profileForm.current_password = ''
    profileForm.password = ''
    profileForm.password_confirmation = ''
  }

  function resetForm() {
    if (currentUser.value) {
      populateForm(currentUser.value)
    }
  }

  function validateForm() {
    const errors = {}
    
    if (!profileForm.name.trim()) {
      errors.name = 'Il nome è obbligatorio'
    }
    
    if (!profileForm.email.trim()) {
      errors.email = 'L\'email è obbligatoria'
    }
    
    if (profileForm.password) {
      if (!profileForm.current_password) {
        errors.current_password = 'Inserisci la password corrente'
      }
      
      if (profileForm.password !== profileForm.password_confirmation) {
        errors.password_confirmation = 'Le password non corrispondono'
      }
    }
    
    return {
      isValid: Object.keys(errors).length === 0,
      errors
    }
  }

  function clearError() {
    error.value = null
  }

  if (currentUser.value) {
    populateForm(currentUser.value)
  }

  return {
    loading: readonly(loading),
    updating: readonly(updating),
    error: readonly(error),
    profileForm,
    currentUser,
    fetchProfile,
    updateProfile,
    deleteAccount,
    populateForm,
    resetForm,
    validateForm,
    clearError,
  }
}