<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref, watch, nextTick, onMounted } from 'vue';

const props = defineProps<{
    administration: any;
    messages: any;
}>();

const messagesContainer = ref<HTMLElement | null>(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// Scroll to bottom when messages change
watch(() => props.messages, () => {
    scrollToBottom();
}, { deep: true });

// Scroll to bottom on initial load
onMounted(() => {
    scrollToBottom();
});

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

// Function to check if a message is the last tool call in the chat
const isLastToolCall = (index: number) => {
    // If this is the last message or there's a subsequent assistant message with content,
    // then this is the last tool call
    return index === props.messages.length - 1 || 
           (props.messages[index + 1] && props.messages[index + 1].role === 'assistant' && props.messages[index + 1].content);
};
</script>


<template>
    <AuthenticatedLayout>   
        
        <div class="flex flex-col h-screen max-h-screen">
            <div class="flex flex-row justify-between items-center w-full p-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="fill-blue-500 h-10 w-10" version="1.0" width="1024.000000pt" height="1024.000000pt" viewBox="0 0 1024.000000 1024.000000" preserveAspectRatio="xMidYMid meet">

            <g transform="translate(0.000000,1024.000000) scale(0.100000,-0.100000)" class="fill-blue-500" height="25px" stroke="none">
            <path d="M2540 8704 c-374 -50 -740 -207 -1030 -441 -106 -86 -292 -281 -378 -395 l-73 -97 -52 20 c-122 49 -131 51 -143 34 -28 -38 -30 -471 -4 -660 9 -62 9 -102 -2 -180 -19 -144 -19 -2590 0 -2715 33 -218 93 -410 187 -605 107 -222 224 -383 401 -556 302 -293 636 -460 1094 -546 88 -16 212 -18 1748 -20 1593 -2 1654 -3 1695 -21 23 -10 103 -66 177 -124 337 -263 760 -528 1172 -733 241 -120 334 -156 407 -157 82 -1 145 37 170 105 7 19 11 202 11 541 l0 513 23 20 c12 12 50 35 84 52 609 304 1028 903 1103 1576 8 70 10 483 8 1375 l-4 1275 -27 125 c-117 531 -395 959 -821 1265 -178 128 -475 258 -716 314 -223 53 -144 51 -2623 50 -1913 -1 -2320 -3 -2407 -15z m4770 -609 c334 -45 598 -179 831 -423 151 -157 264 -344 328 -544 58 -181 62 -228 61 -720 l0 -448 40 -20 c51 -26 117 -85 136 -121 18 -36 18 -92 -1 -130 -15 -27 -111 -99 -134 -99 -5 0 -16 -7 -24 -15 -13 -12 -15 -102 -16 -607 -2 -548 -3 -600 -21 -690 -70 -340 -268 -656 -545 -871 -139 -107 -330 -193 -535 -242 -41 -9 -81 -24 -87 -33 -10 -12 -13 -102 -13 -373 0 -355 0 -358 -22 -373 -27 -19 -21 -23 -253 142 -228 162 -409 299 -527 399 -111 94 -330 315 -320 323 4 4 48 24 97 45 256 107 530 276 737 455 263 226 474 512 626 845 62 138 115 298 178 534 31 118 55 193 68 208 12 12 154 86 317 162 318 150 365 175 398 217 26 33 26 48 0 90 -42 69 -202 159 -564 316 -88 38 -168 76 -178 85 -9 9 -27 48 -38 87 -90 311 -131 408 -242 570 -34 50 -111 141 -172 202 -212 213 -441 332 -768 398 -201 41 -331 49 -647 43 -298 -6 -353 -11 -985 -76 -815 -84 -984 -95 -1475 -95 -413 1 -582 11 -950 60 -316 42 -900 168 -900 195 0 14 169 177 237 228 198 148 419 238 675 275 142 21 4536 22 4688 1z m-5934 -894 c15 -15 221 -115 314 -154 398 -163 1076 -287 1674 -304 l208 -6 -83 -54 c-112 -72 -358 -192 -495 -242 -294 -106 -582 -147 -869 -122 -282 25 -404 68 -517 182 -116 118 -206 333 -238 569 -6 47 -13 97 -15 113 -5 27 3 34 21 18z m5288 -471 c138 -35 304 -140 392 -248 47 -58 117 -194 152 -298 40 -117 55 -269 49 -494 -6 -227 -23 -358 -68 -530 -88 -334 -250 -627 -477 -862 -302 -314 -694 -509 -1172 -584 -174 -28 -624 -26 -810 3 -479 75 -850 219 -1245 484 -306 205 -614 513 -801 799 -60 94 -139 240 -129 240 3 0 58 -26 123 -59 749 -375 1422 -468 1866 -258 123 59 198 110 279 194 127 130 194 236 361 569 58 116 130 256 162 310 239 416 532 659 886 733 130 27 328 28 432 1z m-5105 -820 c35 -47 134 -155 220 -240 l157 -155 24 -90 c93 -360 284 -750 521 -1065 436 -579 1052 -975 1800 -1160 202 -49 194 -46 119 -54 -36 -4 -443 -6 -905 -4 -827 4 -842 5 -940 27 -554 125 -985 548 -1111 1091 -15 66 -18 168 -24 880 -7 748 -10 841 -36 990 -6 31 0 26 53 -50 32 -47 87 -123 122 -170z m329 116 c170 -86 500 -195 712 -235 63 -13 122 -25 130 -28 25 -8 -94 -109 -157 -133 -73 -27 -183 -27 -266 1 -166 55 -343 204 -451 377 -14 23 -24 42 -21 42 3 0 27 -11 53 -24z"/>
            <path d="M6357 6380 c-95 -24 -184 -97 -228 -185 -21 -44 -24 -64 -24 -155 0 -89 4 -112 23 -151 37 -75 87 -125 160 -161 60 -30 75 -33 157 -33 78 0 97 4 147 28 259 127 261 501 4 627 -69 34 -171 47 -239 30z"/>
            </g>
            </svg>
                <div class="flex flex-col">
                    <Link :href="route('moneybird.logout')" class="text-blue-500 hover:text-blue-600">
                        Logout
                    </Link>
                </div>
            </div>
            
            <div class="flex-1 flex flex-col px-4 pb-4 overflow-hidden">
                <div ref="messagesContainer" class="flex-1 overflow-y-auto border border-blue-500 rounded-lg p-4 mb-4">
                    <div v-if="messages" class="flex flex-col gap-2">
                        <div v-for="(message, messageIndex) in messages" :key="messageIndex" class="flex flex-col">
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
                                <div v-for="(toolCall, index) in message.tool_calls" :key="index" class="flex justify-start">
                                    <div class="bg-gray-100 p-3 rounded-lg max-w-[60%] break-words italic text-gray-600 flex items-center gap-2">
                                        <!-- Show loading spinner if it's the last tool call, otherwise show checkmark -->
                                        <svg v-if="isLastToolCall(messageIndex)" class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="h-4 w-4 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Running tool: {{ toolCall.function.name }}...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-row justify-between gap-2">
                    <Button class="" @click="clearChat">Chat leegmaken</Button>
                    <Input type="text" placeholder="Type je bericht" class="w-full border-blue-500" v-model="newMessage.message" @keyup.enter="sendMessage" />
                    <Button class="" @click="sendMessage">Verstuur</Button>
                </div>
            </div>
        </div>
        
    </AuthenticatedLayout>
</template>