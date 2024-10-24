import sqlite3
from datetime import datetime
import uuid
from typing import List, Dict, Any

class PmdAIDB:
    def __init__(self, db_path: str = "pmdai.db"):
        self.db_path = db_path
        self.init_db()

    def init_db(self):
        print(f"Initializing database at {self.db_path}")
        conn = sqlite3.connect(self.db_path)
        c = conn.cursor()
        c.execute('''CREATE TABLE IF NOT EXISTS conversations
                     (id TEXT PRIMARY KEY, created_at TIMESTAMP, updated_at TIMESTAMP, title TEXT)''')
        c.execute('''CREATE TABLE IF NOT EXISTS messages
                     (id TEXT PRIMARY KEY, conversation_id TEXT, timestamp TIMESTAMP, role TEXT, content TEXT)''')
        conn.commit()
        conn.close()
        print("Database initialized successfully")

    def create_conversation(self, title: str = "New Conversation") -> str:
        print(f"Creating new conversation: {title}")
        conversation_id = str(uuid.uuid4())
        now = datetime.now().isoformat()
        try:
            conn = sqlite3.connect(self.db_path)
            c = conn.cursor()
            c.execute("INSERT INTO conversations (id, created_at, updated_at, title) VALUES (?, ?, ?, ?)",
                      (conversation_id, now, now, title))
            conn.commit()
            conn.close()
            print(f"Conversation created successfully with ID: {conversation_id}")
            return conversation_id
        except Exception as e:
            print(f"Error creating conversation: {str(e)}")
            return None

    def add_message(self, conversation_id: str, role: str, content: str) -> str:
        print(f"Adding message to conversation {conversation_id}")
        message_id = str(uuid.uuid4())
        now = datetime.now().isoformat()
        try:
            conn = sqlite3.connect(self.db_path)
            c = conn.cursor()
            c.execute("INSERT INTO messages (id, conversation_id, timestamp, role, content) VALUES (?, ?, ?, ?, ?)",
                      (message_id, conversation_id, now, role, content))
            c.execute("UPDATE conversations SET updated_at = ? WHERE id = ?", (now, conversation_id))
            conn.commit()
            conn.close()
            print(f"Message added successfully with ID: {message_id}")
            return message_id
        except Exception as e:
            print(f"Error adding message: {str(e)}")
            return None

    def get_conversation_messages(self, conversation_id: str, limit: int = 20) -> List[Dict[str, Any]]:
        print(f"Getting messages for conversation {conversation_id}")
        try:
            conn = sqlite3.connect(self.db_path)
            c = conn.cursor()
            c.execute("SELECT role, content FROM messages WHERE conversation_id = ? ORDER BY timestamp DESC LIMIT ?",
                      (conversation_id, limit))
            messages = [{"role": row[0], "content": row[1]} for row in c.fetchall()]
            conn.close()
            print(f"Retrieved {len(messages)} messages")
            return list(reversed(messages))
        except Exception as e:
            print(f"Error getting conversation messages: {str(e)}")
            return []

    def get_conversations(self) -> List[Dict[str, Any]]:
        print("Getting all conversations")
        try:
            conn = sqlite3.connect(self.db_path)
            c = conn.cursor()
            c.execute("SELECT id, created_at, updated_at, title FROM conversations ORDER BY updated_at DESC")
            conversations = [{"id": row[0], "created_at": row[1], "updated_at": row[2], "title": row[3]} for row in c.fetchall()]
            conn.close()
            print(f"Retrieved {len(conversations)} conversations")
            return conversations
        except Exception as e:
            print(f"Error getting conversations: {str(e)}")
            return []

    def search_conversations(self, query: str) -> List[Dict[str, Any]]:
        print(f"Searching conversations with query: {query}")
        try:
            conn = sqlite3.connect(self.db_path)
            c = conn.cursor()
            c.execute("""
                SELECT DISTINCT c.id, c.created_at, c.updated_at, c.title
                FROM conversations c
                JOIN messages m ON c.id = m.conversation_id
                WHERE c.title LIKE ? OR m.content LIKE ?
                ORDER BY c.updated_at DESC
            """, (f"%{query}%", f"%{query}%"))
            conversations = [{"id": row[0], "created_at": row[1], "updated_at": row[2], "title": row[3]} for row in c.fetchall()]
            conn.close()
            print(f"Found {len(conversations)} conversations matching the query")
            return conversations
        except Exception as e:
            print(f"Error searching conversations: {str(e)}")
            return []