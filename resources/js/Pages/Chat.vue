<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    administration: any;
    messages: any;
}>();

const newMessage = useForm({
    message: '',
});

const sendMessage = () => {
    newMessage.post('/chat');
    newMessage.message = '';
}

const clearChat = () => {
    router.post('/chat/clear');
}

const isUserMessage = (message: any) => message.role === 'user';
const isAssistantMessage = (message: any) => message.role === 'assistant' && message.content;
const isToolCall = (message: any) => message.role === 'assistant' && message.tool_calls;
const isToolResponse = (message: any) => message.role === 'tool';
</script>


<template>
    <AuthenticatedLayout>   
        
        <div class="flex p-4 justify-center flex-col items-center h-screen w-full gap-4">
            <div class="flex flex-row justify-between items-center w-full">
                    <img src="https://images.prismic.io/moneybird/ZkXWryol0Zci9Mhh_vertical_birdblue.png?auto=format,compress" class="w-10 h-10" />
                    <div class="flex flex-col">
                     <Link :href="route('moneybird.logout')" class="text-blue-500 hover:text-blue-600">
                        Logout
                     </Link>
                    </div>
                
            </div>
            <div class=" rounded-lg w-full h-full flex flex-col justify-between gap-4">
               
                <div class="flex flex-col flex-grow-1 overflow-y-auto border border-blue-500 rounded-lg h-full p-4">
                    <div v-if="messages" class="flex flex-col gap-2">
                        <div v-for="(message, index) in messages" :key="index" class="flex flex-col">
                            <!-- User message -->
                            <div v-if="isUserMessage(message)" class="flex justify-end">
                                <div class="bg-blue-500 text-white p-3 rounded-lg max-w-[70%] break-words">
                                    {{ message.content }}
                                </div>
                            </div>
                            
                            <!-- Assistant message -->
                            <div v-if="isAssistantMessage(message)" class="flex justify-start">
                                <div class="bg-gray-200 p-3 rounded-lg max-w-[70%] break-words">
                                    {{ message.content }}
                                </div>
                            </div>
                            
                            <!-- Tool call -->  
                            <div v-if="isToolCall(message)" class="flex flex-col gap-2">
                                <div v-for="(toolCall, index) in message.tool_calls" :key="index" class="flex flex-col">
                                    <div class="bg-gray-100 p-3 rounded-lg max-w-[70%] break-words italic text-gray-600">
                                        Running tool: {{ toolCall.function.name }}...
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="flex flex-row justify-between gap-2">
                    <Button class="" @click="clearChat">Clear chat</Button>
                    <Input type="text" placeholder="Type je bericht" class="w-full border-blue-500" v-model="newMessage.message" @keyup.enter="sendMessage" />
                    <Button class="" @click="sendMessage">Verstuur</Button>
                </div>
            </div>
         
        </div>
        
    </AuthenticatedLayout>
</template>