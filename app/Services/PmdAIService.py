import sys
import os
import json
import traceback
import logging

# Debug information
print(f"Python version: {sys.version}")
print(f"sys.path: {sys.path}")
print(f"Current working directory: {os.getcwd()}")
try:
    import anthropic
    print("anthropic module successfully imported")
except ImportError as e:
    print(f"Failed to import anthropic: {str(e)}")

# Configure logging
logging.basicConfig(filename='pmdai_service.log', level=logging.DEBUG, 
                    format='%(asctime)s - %(levelname)s - %(message)s')

# Add the directory containing PMDAI_API.py to the Python path
sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))

from PMDAI_API import PmdAIApi

class PmdAIService:
    def __init__(self):
        try:
            self.api = PmdAIApi()
            logging.info("PmdAIApi initialized successfully")
        except Exception as e:
            logging.error(f"Failed to initialize PmdAIApi: {str(e)}")
            print(json.dumps({'error': f'Failed to initialize PmdAIApi: {str(e)}'}))
            sys.exit(1)

    def send_message(self, conversation_id, message, image_data=None):
        try:
            logging.info(f"Sending message. Conversation ID: {conversation_id}, Message: {message[:50]}...")
            logging.debug(f"Full message: {message}")
            if image_data:
                logging.info("Image data provided")
            
            response, input_tokens, output_tokens, cost = self.api.send_message(conversation_id, message, image_data)
            
            logging.info(f"Message sent successfully. Input tokens: {input_tokens}, Output tokens: {output_tokens}")
            logging.debug(f"Full response: {response}")
            
            result = {
                'message': response,
                'tokens_up': input_tokens,
                'tokens_down': output_tokens,
                'cost': cost
            }
            logging.info(f"Returning result: {json.dumps(result)}")
            return result
        except Exception as e:
            logging.error(f"Error in send_message: {str(e)}")
            logging.error(traceback.format_exc())
            return {'error': f'Error in send_message: {str(e)}'}

    def create_conversation(self, title):
        try:
            logging.info(f"Creating conversation with title: {title}")
            conversation_id = self.api.create_conversation(title)
            logging.info(f"Conversation created successfully. ID: {conversation_id}")
            return {'id': conversation_id, 'name': title}
        except Exception as e:
            logging.error(f"Error in create_conversation: {str(e)}")
            return {'error': f'Error in create_conversation: {str(e)}'}

    def get_conversations(self):
        try:
            logging.info("Retrieving conversations")
            conversations = self.api.get_conversations()
            logging.info(f"Retrieved {len(conversations)} conversations")
            return conversations
        except Exception as e:
            logging.error(f"Error in get_conversations: {str(e)}")
            return {'error': f'Error in get_conversations: {str(e)}'}

    def get_total_usage(self):
        try:
            logging.info("Retrieving total usage")
            tokens_up, tokens_down, total_cost = self.api.get_total_usage()
            logging.info(f"Total usage retrieved. Tokens up: {tokens_up}, Tokens down: {tokens_down}, Total cost: {total_cost}")
            return {
                'tokens_up': tokens_up,
                'tokens_down': tokens_down,
                'total_cost': total_cost
            }
        except Exception as e:
            logging.error(f"Error in get_total_usage: {str(e)}")
            return {'error': f'Error in get_total_usage: {str(e)}'}

if __name__ == "__main__":
    try:
        logging.info("PmdAIService script started")
        service = PmdAIService()
        
        if len(sys.argv) < 2:
            logging.error("No command provided")
            print(json.dumps({'error': 'No command provided'}))
            sys.exit(1)

        command = sys.argv[1]
        logging.info(f"Received command: {command}")

        if command == 'send_message':
            if len(sys.argv) < 4:
                logging.error("Invalid arguments for send_message")
                print(json.dumps({'error': 'Invalid arguments for send_message'}))
                sys.exit(1)
            conversation_id = sys.argv[2] if sys.argv[2] != 'null' else None
            message = sys.argv[3]
            image_data = sys.argv[4] if len(sys.argv) > 4 else None
            result = service.send_message(conversation_id, message, image_data)

        elif command == 'create_conversation':
            if len(sys.argv) < 3:
                logging.error("Invalid arguments for create_conversation")
                print(json.dumps({'error': 'Invalid arguments for create_conversation'}))
                sys.exit(1)
            title = sys.argv[2]
            result = service.create_conversation(title)

        elif command == 'get_conversations':
            result = service.get_conversations()

        elif command == 'get_total_usage':
            result = service.get_total_usage()

        else:
            logging.error(f"Invalid command: {command}")
            result = {'error': 'Invalid command'}

        logging.info(f"Command execution result: {result}")
        print(json.dumps(result))

    except Exception as e:
        logging.error(f"Unhandled exception: {str(e)}")
        logging.error(traceback.format_exc())
        print(json.dumps({'error': f'Unhandled exception: {str(e)}', 'traceback': traceback.format_exc()}))
        sys.exit(1)

logging.info("PmdAIService script completed")