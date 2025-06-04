<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">🎉 Example Component</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">
                            Questo è un componente di esempio che utilizza:
                        </p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>🅱️ Bootstrap</h6>
                                <button class="btn btn-primary me-2">Primary</button>
                                <button class="btn btn-success">Success</button>
                            </div>
                            <div class="col-md-6">
                                <h6>🎨 Tailwind CSS</h6>
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded me-2">
                                    Blue
                                </button>
                                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Green
                                </button>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>⚡ Vue.js 3:</strong> Counter reattivo: 
                            <span class="badge bg-secondary fs-6">{{ count }}</span>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button @click="increment" class="btn btn-success">
                                ➕ Incrementa
                            </button>
                            <button @click="decrement" class="btn btn-warning">
                                ➖ Decrementa
                            </button>
                            <button @click="reset" class="btn btn-danger">
                                🔄 Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue'

export default {
    name: 'ExampleComponent',
    setup() {
        const count = ref(0)
        
        const increment = () => {
            count.value++
        }
        
        const decrement = () => {
            count.value--
        }
        
        const reset = () => {
            count.value = 0
        }
        
        return {
            count,
            increment,
            decrement,
            reset
        }
    }
}
</script>
