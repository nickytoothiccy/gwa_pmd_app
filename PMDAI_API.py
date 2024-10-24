import anthropic
from typing import List, Dict, Any, Tuple, Generator
import traceback
from PMDAI_DB import PmdAIDB

class PmdAIApi:
    def __init__(self, api_key: str = "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", db_path: str = "pmdai.db"):
        print("Initializing PmdAIApi...")
        self.api_key = api_key
        self.client = anthropic.Anthropic(api_key=self.api_key)
        self.db = PmdAIDB(db_path)
        self.tokens_up = 0
        self.tokens_down = 0
        self.total_cost = 0

    def create_conversation(self, title: str) -> str:
        return self.db.create_conversation(title)

    def add_message(self, conversation_id: str, role: str, content: str) -> str:
        return self.db.add_message(conversation_id, role, content)

    def get_conversation_messages(self, conversation_id: str, limit: int = 20) -> List[Dict[str, Any]]:
        return self.db.get_conversation_messages(conversation_id, limit)

    def send_message(self, conversation_id: str, user_message: str, image_data: str = None, prompt_template: str = None, model: str = "claude-3-5-sonnet-20240620", max_tokens: int = 1024, stream: bool = False) -> Tuple[str, int, int, float]:
        print(f"Processing message for conversation {conversation_id}")
        print(f"User message: {user_message[:50]}...")
        
        context = self.get_conversation_messages(conversation_id) if conversation_id else []
        
        messages = []
        for msg in context:
            messages.append({"role": msg['role'], "content": msg['content']})
        
        if image_data:
            messages.append({
                "role": "user",
                "content": [
                    {
                        "type": "image",
                        "source": {
                            "type": "base64",
                            "media_type": "image/jpeg",
                            "data": image_data
                        }
                    },
                    {
                        "type": "text",
                        "text": user_message
                    }
                ]
            })
        else:
            messages.append({
                "role": "user",
                "content": user_message
            })
        
        try:
            print("Sending request to Anthropic API...")
            if stream:
                return self.stream_response(conversation_id, user_message, model, max_tokens, messages)
            else:
                response = self.client.messages.create(
                    model=model,
                    max_tokens=max_tokens,
                    messages=messages
                )
                ai_response = response.content[0].text
                print(f"Received AI response: {ai_response[:50]}...")
                
                # Create or update conversation after successful AI response
                if not conversation_id:
                    conversation_title = user_message[:20]
                    conversation_id = self.create_conversation(conversation_title)
                
                # Now save both user and AI messages
                self.add_message(conversation_id, "user", user_message)
                self.add_message(conversation_id, "assistant", ai_response)
                
                # Calculate token usage and cost
                input_tokens = response.usage.input_tokens
                output_tokens = response.usage.output_tokens
                cost = self.calculate_cost(model, input_tokens, output_tokens)
                
                self.tokens_up += input_tokens
                self.tokens_down += output_tokens
                self.total_cost += cost
                
                return ai_response, input_tokens, output_tokens, cost
        except Exception as e:
            error_code = "ERR_API_RESPONSE"
            error_message = f"An error occurred while processing your request. Error code: {error_code}"
            print(f"Error: {str(e)}\n{traceback.format_exc()}")
            if conversation_id:
                self.add_message(conversation_id, "assistant", error_message)
            return error_message, 0, 0, 0.0

    def stream_response(self, conversation_id: str, user_message: str, model: str, max_tokens: int, messages: List[Dict[str, Any]]) -> Generator[str, None, Tuple[int, int, float]]:
        try:
            print("Streaming response from Anthropic API...")
            full_response = ""
            with self.client.messages.stream(
                model=model,
                max_tokens=max_tokens,
                messages=messages
            ) as stream:
                for message in stream:
                    if message.type == 'content_block_delta':
                        text = message.delta.text
                        full_response += text
                        yield text
                    elif message.type == 'message_stop':
                        break

            # Create or update conversation after successful AI response
            if not conversation_id:
                conversation_title = user_message[:20]
                conversation_id = self.create_conversation(conversation_title)
            
            # Now save both user and AI messages
            self.add_message(conversation_id, "user", user_message)
            self.add_message(conversation_id, "assistant", full_response)

            # Calculate token usage and cost
            # Note: We don't have access to the exact token count in streaming mode,
            # so we'll estimate based on the length of the messages and response
            input_tokens = sum(len(m['content'].split()) if isinstance(m['content'], str) else sum(len(c['text'].split()) for c in m['content'] if c['type'] == 'text') for m in messages)
            output_tokens = len(full_response.split())
            cost = self.calculate_cost(model, input_tokens, output_tokens)

            self.tokens_up += input_tokens
            self.tokens_down += output_tokens
            self.total_cost += cost

            yield (input_tokens, output_tokens, cost)
        except Exception as e:
            error_code = "ERR_STREAM_RESPONSE"
            error_message = f"An error occurred while streaming the response. Error code: {error_code}"
            print(f"Error: {str(e)}\n{traceback.format_exc()}")
            if conversation_id:
                self.add_message(conversation_id, "assistant", error_message)
            yield error_message

    def calculate_cost(self, model: str, input_tokens: int, output_tokens: int) -> float:
        # Pricing for Claude 3.5 Sonnet
        base_input_price_per_1k = 0.003
        output_price_per_1k = 0.015
        
        base_input_cost = (input_tokens / 1000) * base_input_price_per_1k
        output_cost = (output_tokens / 1000) * output_price_per_1k
        
        return base_input_cost + output_cost

    def get_total_usage(self) -> Tuple[int, int, float]:
        return self.tokens_up, self.tokens_down, self.total_cost

    def get_conversations(self) -> List[Dict[str, Any]]:
        return self.db.get_conversations()

    def search_conversations(self, query: str) -> List[Dict[str, Any]]:
        return self.db.search_conversations(query)

# Example usage
if __name__ == "__main__":
    api = PmdAIApi()
    
    # Non-streaming example
    response, input_tokens, output_tokens, cost = api.send_message(None, "Hello, how are you?")
    print(f"AI: {response}")
    print(f"Usage: {input_tokens} input tokens, {output_tokens} output tokens")
    print(f"Cost: ${cost:.6f}")
    
    # Get the created conversation ID
    conversations = api.get_conversations()
    if conversations:
        conversation_id = conversations[0]['id']
        
        # Streaming example
        print("\nStreaming response:")
        for chunk in api.send_message(conversation_id, "Tell me a short story.", stream=True):
            if isinstance(chunk, str):
                print(chunk, end="", flush=True)
            else:
                input_tokens, output_tokens, cost = chunk
                print(f"\nUsage: {input_tokens} input tokens, {output_tokens} output tokens")
                print(f"Cost: ${cost:.6f}")
