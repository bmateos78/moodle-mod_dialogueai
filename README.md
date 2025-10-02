# DialogueAI Activity Module for Moodle

An AI-powered dialogue activity module that enables interactive conversations between students and AI bots using OpenAI's API, with customizable documentation and system prompts.

## Features

- **AI-Powered Conversations**: Integrates with OpenAI API for intelligent dialogue
- **Customizable Bot Configuration**: Set bot name and personality
- **Personalized Experience**: Configure student names for personalized interactions
- **Document-Based Dialogue**: Upload documentation files for context-aware conversations
- **System Prompt Customization**: Define AI behavior and response style
- **Secure API Integration**: Secure handling of OpenAI API keys
- **Moodle Integration**: Full integration with Moodle's activity system
- **File Management**: Support for multiple document formats (PDF, TXT, DOC, DOCX)

## Settings Configuration

The plugin includes the following configurable settings:

1. **Bot Name**: Customize the name of the AI bot
2. **Student Name**: Set the student's name for personalized interactions
3. **Documentation**: Upload files (PDF, TXT, DOC, DOCX) that the AI will reference during dialogue
4. **System Prompt**: Define the AI's behavior, personality, and response guidelines
5. **OpenAI API Key**: Secure field for your OpenAI API credentials

## Installation

1. Download the plugin
2. Extract the contents to the `/mod/dialogueai` directory of your Moodle installation
3. Log in to your Moodle site as an administrator
4. Go to Site administration > Notifications
5. Follow the on-screen instructions to install the plugin

## Usage

1. Enable editing mode in your course
2. Click "Add an activity or resource"
3. Select "DialogueAI" from the activity chooser
4. Configure the activity settings:
   - Set the activity name
   - Configure bot name and student name
   - Upload relevant documentation files
   - Set up the system prompt to guide AI behavior
   - Enter your OpenAI API key
5. Save and display
6. Students can click "Start Dialogue" to begin AI-powered conversations

## Requirements

- Moodle 5.0 or later
- OpenAI API account and API key
- PHP with cURL support for API calls

## Security Notes

- API keys are stored securely in the database
- File uploads are restricted to safe document formats
- All user inputs are properly sanitized

## License

This project is licensed under the GNU General Public License v3.0.

## Author

Your Name <your@email.com>

## Technical Details

This DialogueAI module is built following Moodle coding guidelines and includes:
- Proper database schema with support for additional configuration fields
- File management system for document uploads
- Secure API key handling
- Responsive user interface
- Full Moodle activity lifecycle support

## Future Enhancements

- Real-time chat interface
- Conversation history and analytics
- Multiple AI model support
- Advanced prompt templates
- Integration with Moodle gradebook
