<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps<{
    administration: any;
    messages: any;
}>();

const localMessages = ref(props.messages || []);
const isStreaming = ref(false);
const streamError = ref(false);
const currentToolCall = ref(null);

const newMessage = useForm({
    message: '',
});

// Clean up any active EventSource when component unmounts
let activeEventSource = null;
onBeforeUnmount(() => {
    if (activeEventSource) {
        activeEventSource.close();
        activeEventSource = null;
    }
});

const sendMessage = () => {
    if (!newMessage.message.trim() || isStreaming.value) return;
    
    // Clear previous stream error
    streamError.value = false;
    currentToolCall.value = null;
    
    // Add user message to local messages
    localMessages.value = [...(localMessages.value || []), {
        role: 'user',
        content: newMessage.message,
    }];
    
    const messageToSend = newMessage.message;
    newMessage.message = '';
    
    try {
        isStreaming.value = true;
        
        // Close any existing EventSource
        if (activeEventSource) {
            activeEventSource.close();
            activeEventSource = null;
        }
        
        // Create EventSource for SSE with GET request
        const timestamp = new Date().getTime(); // Add timestamp to prevent caching
        activeEventSource = new EventSource(`/chat/stream?message=${encodeURIComponent(messageToSend)}&_=${timestamp}`);
        
        let assistantMessageAdded = false;
        let assistantMessageIndex = -1;
        let currentAssistantContent = '';
        
        activeEventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                
                // Handle different event types
                switch (data.type) {
                    case 'tool_call':
                        // Show a tool call notification
                        currentToolCall.value = data.tool;
                        
                        // Add tool call message to UI
                        localMessages.value.push({
                            role: 'assistant',
                            tool_calls: [{
                                function: {
                                    name: data.tool.name,
                                    arguments: JSON.stringify(data.tool.parameters)
                                },
                                id: data.tool.id
                            }]
                        });
                        break;
                        
                    case 'tool_result':
                        // Update the tool call status
                        currentToolCall.value = {
                            ...data.tool,
                            completed: true
                        };
                        
                        // Tool calls are not visible in the UI
                        localMessages.value.push({
                            role: 'tool',
                            content: JSON.stringify(data.tool.result),
                            tool_call_id: data.tool.id,
                            hidden: true
                        });
                        break;
                        
                    case 'thinking':
                        // Add a placeholder for the final assistant message
                        assistantMessageAdded = true;
                        localMessages.value.push({
                            role: 'assistant',
                            content: '',
                            isStreaming: true
                        });
                        assistantMessageIndex = localMessages.value.length - 1;
                        break;
                        
                    case 'content':
                        // If we haven't added the assistant message yet (no thinking event)
                        if (!assistantMessageAdded) {
                            assistantMessageAdded = true;
                            localMessages.value.push({
                                role: 'assistant',
                                content: '',
                                isStreaming: true
                            });
                            assistantMessageIndex = localMessages.value.length - 1;
                        }
                        
                        // Update the content
                        if (assistantMessageIndex >= 0) {
                            currentAssistantContent += data.content;
                            localMessages.value[assistantMessageIndex].content = currentAssistantContent;
                        }
                        break;
                        
                    case 'error':
                        streamError.value = true;
                        
                        // Show error in UI
                        if (!assistantMessageAdded) {
                            localMessages.value.push({
                                role: 'assistant',
                                content: data.content || 'Sorry, an error occurred',
                                isError: true
                            });
                        } else if (assistantMessageIndex >= 0) {
                            localMessages.value[assistantMessageIndex].isError = true;
                            if (!localMessages.value[assistantMessageIndex].content) {
                                localMessages.value[assistantMessageIndex].content = data.content || 'Sorry, an error occurred';
                            }
                        }
                        break;
                        
                    case 'done':
                        // Complete the message
                        if (assistantMessageIndex >= 0) {
                            localMessages.value[assistantMessageIndex].isStreaming = false;
                        }
                        
                        // Clean up
                        if (activeEventSource) {
                            activeEventSource.close();
                            activeEventSource = null;
                        }
                        
                        isStreaming.value = false;
                        currentToolCall.value = null;
                        break;
                        
                    default:
                        console.warn('Unknown event type:', data.type);
                }
            } catch (e) {
                console.error('Error parsing stream data:', e, event.data);
            }
        };
        
        activeEventSource.onerror = (error) => {
            console.error('EventSource error:', error);
            streamError.value = true;
            
            if (activeEventSource) {
                activeEventSource.close();
                activeEventSource = null;
            }
            
            isStreaming.value = false;
            currentToolCall.value = null;
            
            // Show an error message to the user if we haven't added a message yet
            if (!assistantMessageAdded) {
                localMessages.value.push({
                    role: 'assistant',
                    content: "Sorry, there was an error processing your request. Please try again.",
                    isError: true
                });
            } else if (assistantMessageIndex >= 0) {
                localMessages.value[assistantMessageIndex].isStreaming = false;
                localMessages.value[assistantMessageIndex].isError = true;
                if (!localMessages.value[assistantMessageIndex].content) {
                    localMessages.value[assistantMessageIndex].content = "Sorry, there was an error processing your request.";
                }
            }
        };
        
    } catch (error) {
        console.error('Error setting up stream:', error);
        streamError.value = true;
        isStreaming.value = false;
        currentToolCall.value = null;
        
        // Show an error message to the user
        localMessages.value.push({
            role: 'assistant',
            content: "Sorry, there was an error processing your request. Please try again.",
            isError: true
        });
    }
}

const isUserMessage = (message: any) => message.role === 'user';
const isAssistantMessage = (message: any) => message.role === 'assistant' && message.content !== null && !message.hidden;
const isToolCall = (message: any) => message.role === 'assistant' && message.tool_calls && !message.hidden;
const isToolResponse = (message: any) => message.role === 'tool' && !message.hidden;
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
                    <div class="flex flex-col gap-4">
                        <div v-for="(message, index) in localMessages" :key="index" class="flex flex-col">
                            <!-- User message -->
                            <div v-if="isUserMessage(message)" class="flex justify-end">
                                <div class="bg-blue-500 text-white p-3 rounded-lg max-w-[70%] break-words">
                                    {{ message.content }}
                                </div>
                            </div>
                            
                            <!-- Assistant message -->
                            <div v-if="isAssistantMessage(message)" class="flex justify-start">
                                <div class="bg-gray-200 p-3 rounded-lg max-w-[70%] break-words" 
                                     :class="{ 'bg-red-100': message.isError }">
                                    {{ message.content }}
                                    <span v-if="message.isStreaming" class="inline-block animate-pulse">▌</span>
                                </div>
                            </div>
                            
                            <!-- Tool call -->
                            <div v-if="isToolCall(message)" class="flex justify-start">
                                <div class="bg-gray-100 p-3 rounded-lg max-w-[70%] break-words italic text-gray-600">
                                    Running tool: {{ message.tool_calls[0].function.name }}...
                                </div>
                            </div>
                            
                            <!-- Currently running tool status -->
                            <div v-if="currentToolCall && index === localMessages.length - 1" class="flex justify-start">
                                <div class="bg-yellow-50 p-3 rounded-lg max-w-[70%] break-words text-gray-700 border border-yellow-200">
                                    <div v-if="currentToolCall.completed">
                                        <span class="font-semibold text-green-600">✓</span> 
                                        Tool {{ currentToolCall.name }} completed
                                    </div>
                                    <div v-else class="flex items-center">
                                        <span class="mr-2 inline-block h-3 w-3 rounded-full bg-yellow-400 animate-pulse"></span>
                                        Running tool: {{ currentToolCall.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-row justify-between gap-2">
                    <Input 
                        type="text" 
                        placeholder="Type je bericht" 
                        class="w-full border-blue-500" 
                        v-model="newMessage.message" 
                        @keyup.enter="sendMessage" 
                        :disabled="isStreaming"
                    />
                    <Button 
                        class="" 
                        @click="sendMessage" 
                        :disabled="isStreaming || !newMessage.message.trim()"
                    >
                        Verstuur
                    </Button>
                </div>
            </div>
         
        </div>
        
    </AuthenticatedLayout>
</template>