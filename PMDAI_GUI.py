import tkinter as tk
from tkinter import ttk, scrolledtext, simpledialog, filedialog, messagebox
from PIL import Image, ImageTk
import base64
import io
from PMDAI_API import PmdAIApi
import threading
import traceback
import os
import csv
import markdown
import html

class PMDAIGUI:
    def __init__(self, master):
        self.master = master
        master.title("PMDAI Chat")
        master.geometry("800x800")
        
        # Set dark mode colors
        self.bg_color = "#2b2b2b"
        self.fg_color = "#ffffff"
        self.entry_bg = "#3c3f41"
        self.button_bg = "#4a4a4a"
        self.highlight_bg = "#323232"

        # Set font size for the chat display
        self.chat_font = ("TkDefaultFont", 12)

        master.configure(bg=self.bg_color)

        print("Initializing PmdAIApi...")
        db_path = os.path.join(os.path.expanduser("~"), "pmdai.db")
        self.api = PmdAIApi(db_path=db_path)
        self.current_conversation_id = None
        self.conversation_map = {}  # Map to store conversation names and IDs
        self.current_image = None
        self.current_image_data = None
        self.streaming_enabled = tk.BooleanVar(value=True)  # New variable for streaming toggle
        self.current_response_start = "0.0"  # Track the start position of the current response

        print("Creating widgets...")
        self.create_widgets()
        print("Loading conversations...")
        self.load_conversations()

    def create_widgets(self):
        style = ttk.Style()
        style.theme_use('default')
        style.configure('TFrame', background=self.bg_color)
        style.configure('TLabel', background=self.bg_color, foreground=self.fg_color)
        style.configure('TButton', background=self.button_bg, foreground=self.fg_color)
        style.configure('TCheckbutton', background=self.bg_color, foreground=self.fg_color)
        style.configure('TCombobox', fieldbackground=self.entry_bg, background=self.button_bg, foreground=self.fg_color)
        style.map('TCombobox', fieldbackground=[('readonly', self.entry_bg)])
        style.map('TButton', background=[('active', self.highlight_bg)])

        # Create a frame for the token usage display
        token_frame = ttk.Frame(self.master)
        token_frame.pack(side=tk.TOP, fill=tk.X, padx=10, pady=5)

        # Create the token usage labels
        self.tokens_up_label = ttk.Label(token_frame, text="↑ 0")
        self.tokens_up_label.pack(side=tk.LEFT, padx=(0, 10))
        
        self.tokens_down_label = ttk.Label(token_frame, text="↓ 0")
        self.tokens_down_label.pack(side=tk.LEFT, padx=(0, 10))
        
        self.cache_label = ttk.Label(token_frame, text="Cache: ⚡ 0")
        self.cache_label.pack(side=tk.LEFT, padx=(0, 10))
        
        self.cost_label = ttk.Label(token_frame, text="API Cost: $0.0000")
        self.cost_label.pack(side=tk.LEFT, padx=(0, 10))

        # Create the export button
        self.export_button = ttk.Button(token_frame, text="EXPORT", command=self.export_usage)
        self.export_button.pack(side=tk.RIGHT)

        # Create a frame for the chat display and scrollbar
        chat_frame = ttk.Frame(self.master)
        chat_frame.pack(expand=True, fill=tk.BOTH, padx=10, pady=10)

        # Create the chat display with larger font
        self.chat_display = scrolledtext.ScrolledText(chat_frame, wrap=tk.WORD, state='disabled', bg=self.bg_color, fg=self.fg_color, font=self.chat_font)
        self.chat_display.pack(expand=True, fill=tk.BOTH)
        self.chat_display.tag_configure("user", foreground="#ffffff")
        self.chat_display.tag_configure("assistant", foreground="#28a745")

        # Create a frame for the image preview and analysis
        image_frame = ttk.Frame(self.master)
        image_frame.pack(fill=tk.X, padx=10, pady=5)

        # Create the image preview label
        self.image_preview = ttk.Label(image_frame)
        self.image_preview.pack(side=tk.LEFT, padx=(0, 10))

        # Create the image analysis display
        self.image_analysis = scrolledtext.ScrolledText(image_frame, wrap=tk.WORD, width=40, height=5, bg=self.entry_bg, fg=self.fg_color)
        self.image_analysis.pack(side=tk.LEFT, expand=True, fill=tk.BOTH)

        # Create the input box
        self.input_box = ttk.Entry(self.master, style='TEntry')
        self.input_box.pack(fill=tk.X, padx=10, pady=(0, 5))
        self.input_box.bind("<Return>", self.send_message)

        # Create a frame for buttons
        button_frame = ttk.Frame(self.master)
        button_frame.pack(fill=tk.X, padx=10, pady=(0, 10))

        # Create the send button
        self.send_button = ttk.Button(button_frame, text="Send", command=self.send_message)
        self.send_button.pack(side=tk.LEFT, padx=(0, 5))

        # Create the image upload button
        self.upload_button = ttk.Button(button_frame, text="Upload Image", command=self.upload_image)
        self.upload_button.pack(side=tk.LEFT)

        # Create the streaming toggle checkbox
        self.streaming_checkbox = ttk.Checkbutton(button_frame, text="Enable Streaming", variable=self.streaming_enabled, style='TCheckbutton')
        self.streaming_checkbox.pack(side=tk.LEFT, padx=(10, 0))

        # Create the status label
        self.status_label = ttk.Label(self.master, text="")
        self.status_label.pack(pady=(0, 10))

        # Create the conversation dropdown
        self.conversation_var = tk.StringVar(self.master)
        self.conversation_dropdown = ttk.Combobox(self.master, textvariable=self.conversation_var, style='TCombobox')
        self.conversation_dropdown.pack(pady=(0, 10))
        self.conversation_dropdown.bind("<<ComboboxSelected>>", self.load_selected_conversation)

        # Create the new conversation button
        new_conversation_button = ttk.Button(self.master, text="New Conversation", command=self.create_new_conversation)
        new_conversation_button.pack(pady=(0, 10))

    def truncate_title(self, title, max_length=30):
        return title[:max_length] + "..." if len(title) > max_length else title

    def load_conversations(self):
        conversations = self.api.get_conversations()
        self.conversation_map = {self.truncate_title(conv['title']): conv['id'] for conv in conversations}
        self.conversation_dropdown['values'] = list(self.conversation_map.keys())
        
        # Set the current conversation to None instead of creating a new one
        self.current_conversation_id = None
        self.conversation_dropdown.set('')

    def load_selected_conversation(self, event):
        selected_title = self.conversation_var.get()
        self.current_conversation_id = self.conversation_map[selected_title]
        self.load_conversation_messages(self.current_conversation_id)

    def load_conversation_messages(self, conversation_id):
        messages = self.api.get_conversation_messages(conversation_id)
        self.chat_display.config(state='normal')
        self.chat_display.delete('1.0', tk.END)
        for message in messages:
            self.display_message(message['role'], message['content'])
        self.chat_display.config(state='disabled')
        self.chat_display.see(tk.END)

    def create_new_conversation(self):
        name = simpledialog.askstring("New Conversation", "Enter a name for the new conversation:")
        if name:
            print(f"Creating new conversation: {name}")
            conversation_id = self.api.create_conversation(name)
            if conversation_id:
                print(f"Conversation created with ID: {conversation_id}")
                self.current_conversation_id = conversation_id
                truncated_name = self.truncate_title(name)
                self.conversation_map[truncated_name] = conversation_id
                self.conversation_dropdown['values'] = [truncated_name] + list(self.conversation_dropdown['values'])
                self.conversation_dropdown.set(truncated_name)
                self.load_conversation_messages(conversation_id)
            else:
                messagebox.showerror("Error", "Failed to create new conversation")

    def send_message(self, event=None):
        print("Sending message...")
        user_message = self.input_box.get()
        if user_message:
            self.input_box.delete(0, tk.END)
            self.display_message("user", user_message)
            
            # Disable send button and show "Generating response..." message
            self.send_button.config(state=tk.DISABLED)
            self.status_label.config(text="Generating response...")
            
            # Use a thread to prevent GUI freezing
            threading.Thread(target=self.get_ai_response, args=(user_message,), daemon=True).start()
        else:
            print("Cannot send empty message")

    def get_ai_response(self, user_message):
        print(f"Getting AI response for message: {user_message[:50]}...")
        try:
            if self.streaming_enabled.get():
                self.master.after(0, self.prepare_streaming_display)
                full_response = ""
                for chunk in self.api.send_message(self.current_conversation_id, user_message, self.current_image_data, stream=True):
                    if isinstance(chunk, str):
                        full_response += chunk
                        self.master.after(0, self.update_streaming_display, chunk)
                    elif isinstance(chunk, tuple) and len(chunk) == 4:
                        tokens_up, tokens_down, cache_hits, cost = chunk
                        self.master.after(0, self.finish_streaming_display, full_response, tokens_up, tokens_down, cache_hits, cost)
                    else:
                        print(f"Unexpected chunk type: {type(chunk)}")
            else:
                response, tokens_up, tokens_down, cache_hits, cost = self.api.send_message(self.current_conversation_id, user_message, self.current_image_data, stream=False)
                self.master.after(0, self.display_message, "assistant", response)
                self.master.after(0, self.update_token_usage, tokens_up, tokens_down, cache_hits, cost)
                self.master.after(0, self.finish_non_streaming_display)
            
            # Update conversation list if a new conversation was created
            if not self.current_conversation_id:
                self.master.after(0, self.update_conversation_list)
        except Exception as e:
            error_message = f"Error getting AI response: {str(e)}\n{traceback.format_exc()}"
            print(error_message)
            self.master.after(0, self.display_error, error_message)
        finally:
            # Clear the current image data after sending
            self.current_image_data = None
            self.master.after(0, self.clear_image_preview)

    def update_conversation_list(self):
        conversations = self.api.get_conversations()
        self.conversation_map = {self.truncate_title(conv['title']): conv['id'] for conv in conversations}
        self.conversation_dropdown['values'] = list(self.conversation_map.keys())
        
        # Set the current conversation to the newly created one
        if conversations:
            newest_conversation = conversations[0]
            self.current_conversation_id = newest_conversation['id']
            truncated_title = self.truncate_title(newest_conversation['title'])
            self.conversation_dropdown.set(truncated_title)

    def prepare_streaming_display(self):
        self.chat_display.config(state='normal')
        self.current_response_start = self.chat_display.index(tk.END)
        self.chat_display.insert(tk.END, "Assistant: ", "assistant")
        self.chat_display.config(state='disabled')
        self.chat_display.see(tk.END)

    def update_streaming_display(self, new_chunk):
        self.chat_display.config(state='normal')
        self.chat_display.insert(tk.END, new_chunk, "assistant")
        self.chat_display.config(state='disabled')
        self.chat_display.see(tk.END)

    def finish_streaming_display(self, full_response, tokens_up, tokens_down, cache_hits, cost):
        self.chat_display.config(state='normal')
        self.chat_display.insert(tk.END, "\n\n", "assistant")
        self.chat_display.config(state='disabled')
        self.chat_display.see(tk.END)
        self.send_button.config(state=tk.NORMAL)
        self.status_label.config(text="")
        self.update_token_usage(tokens_up, tokens_down, cache_hits, cost)

    def finish_non_streaming_display(self):
        self.send_button.config(state=tk.NORMAL)
        self.status_label.config(text="")

    def display_error(self, error_message):
        self.chat_display.config(state='normal')
        self.chat_display.insert(tk.END, f"Error: {error_message}\n\n", "error")
        self.chat_display.see(tk.END)
        self.chat_display.config(state='disabled')
        self.send_button.config(state=tk.NORMAL)
        self.status_label.config(text="An error occurred")

    def update_token_usage(self, tokens_up, tokens_down, cache_hits, cost):
        total_tokens_up, total_tokens_down, total_cache_hits, total_cost = self.api.get_total_usage()
        self.tokens_up_label.config(text=f"↑ {total_tokens_up}")
        self.tokens_down_label.config(text=f"↓ {total_tokens_down}")
        self.cache_label.config(text=f"Cache: ⚡ {total_cache_hits}")
        self.cost_label.config(text=f"API Cost: ${total_cost:.4f}")

    def display_message(self, role, content):
        self.chat_display.config(state='normal')
        self.chat_display.insert(tk.END, f"{role.capitalize()}: {content}\n\n", role.lower())
        self.chat_display.see(tk.END)
        self.chat_display.config(state='disabled')

    def upload_image(self):
        file_path = filedialog.askopenfilename(filetypes=[("Image files", "*.png *.jpg *.jpeg *.gif *.bmp")])
        if file_path:
            with open(file_path, "rb") as image_file:
                self.current_image_data = base64.b64encode(image_file.read()).decode("utf-8")
            
            # Display image preview
            image = Image.open(file_path)
            image.thumbnail((100, 100))  # Resize image for preview
            photo = ImageTk.PhotoImage(image)
            self.image_preview.config(image=photo)
            self.image_preview.image = photo  # Keep a reference

            # Clear image analysis
            self.image_analysis.config(state='normal')
            self.image_analysis.delete('1.0', tk.END)
            self.image_analysis.insert(tk.END, "Image uploaded. Send a message to analyze.")
            self.image_analysis.config(state='disabled')

    def clear_image_preview(self):
        self.image_preview.config(image='')
        self.image_analysis.config(state='normal')
        self.image_analysis.delete('1.0', tk.END)
        self.image_analysis.config(state='disabled')

    def export_usage(self):
        file_path = filedialog.asksaveasfilename(defaultextension=".csv", filetypes=[("CSV files", "*.csv")])
        if file_path:
            total_tokens_up, total_tokens_down, total_cache_hits, total_cost = self.api.get_total_usage()
            with open(file_path, 'w', newline='') as csvfile:
                writer = csv.writer(csvfile)
                writer.writerow(["Metric", "Value"])
                writer.writerow(["Tokens Up", total_tokens_up])
                writer.writerow(["Tokens Down", total_tokens_down])
                writer.writerow(["Cache Hits", total_cache_hits])
                writer.writerow(["Total API Cost", f"${total_cost:.4f}"])
            messagebox.showinfo("Export Successful", f"Usage data exported to {file_path}")

if __name__ == "__main__":
    print("Starting PMDAI GUI...")
    root = tk.Tk()
    gui = PMDAIGUI(root)
    print("Entering main loop...")
    root.mainloop()
    print("GUI closed.")