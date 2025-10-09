# DialogueAI Activity Module for Moodle

An AI-powered dialogue activity module that enables interactive conversations between students and AI bots using OpenAI's API, with customizable documentation and system prompts.

## 🚀 Features

### ✅ Current Features
- **AI-Powered Conversations**: Engage students in interactive dialogues with AI assistants
- **Multiple OpenAI Models**: Choose between GPT-3.5 Turbo, GPT-4 Turbo, and GPT-4o
- **Dynamic Character Limits**: 50K chars (GPT-3.5) or 475K chars (GPT-4 models)
- **Customizable Bot Personalities**: Set custom names and behaviors for AI tutors
- **Educational Focus**: Designed specifically for learning environments
- **Easy Integration**: Seamless installation in Moodle courses
- **Conversation Management**: Track and manage student interactions
- **Documentation Support**: Upload supporting materials for AI context (text files)
- **Activity Completion**: Automatic completion tracking based on conversation progress
- **Restart Functionality**: Allow students to restart conversations with confirmation
- **Line Break Support**: Proper formatting of AI responses with line breaks
- **Rate Limiting**: Built-in protection against rapid-fire requests
- **Error Handling**: Robust error handling with retry logic
- **Responsive Design**: Mobile-friendly chat interface

## Requirements

- Moodle 5.0.0 or higher
- OpenAI API key
- PHP 8.0 or higher
- Modern web browser with JavaScript enabled

## Installation

1. Download the latest release (`dialogueai-plugin-model-selection.zip`)
2. Extract the zip file to your Moodle's `mod/` directory
3. Rename the extracted folder to `dialogueai` if necessary
4. Visit your Moodle site as an administrator
5. Navigate to Site Administration > Notifications
6. Follow the installation prompts
7. Configure your OpenAI API key in individual activities

## Configuration

### OpenAI API Setup
1. Obtain an API key from [OpenAI](https://platform.openai.com/api-keys)
2. When creating a DialogueAI activity, enter your API key in the activity settings
3. Choose your preferred OpenAI model based on your needs and budget

### Creating a DialogueAI Activity
1. Turn editing on in your course
2. Click "Add an activity or resource"
3. Select "DialogueAI" from the activities list
4. Configure the activity settings:
   - **Activity Name**: Give your activity a descriptive name
   - **Bot Name**: Set the name for your AI assistant
   - **Student Name**: Set how the AI should address students
   - **Welcome Message**: Optional greeting message
   - **Difficulty Level**: Set question difficulty (1-5)
   - **Number of Questions**: How many questions before completion
   - **OpenAI Model**: Choose between GPT-3.5 Turbo, GPT-4 Turbo, or GPT-4o
   - **Documentation**: Upload supporting materials (text files only)
   - **System Prompt**: Instructions that guide the AI's behavior
   - **API Key**: Enter your OpenAI API key
   - **Completion Settings**: Enable "Student must complete the conversation"

### Model Selection Guide

| Model | Character Limit | Speed | Cost | Best For |
|-------|----------------|-------|------|----------|
| **GPT-3.5 Turbo** | 50,000 | Fast | Low | Basic conversations, limited documentation |
| **GPT-4 Turbo** | 475,000 | Moderate | High | Complex analysis, large documentation |
| **GPT-4o** | 475,000 | Fast | Medium | Balanced premium experience |

## Usage

### For Students
1. Click on the DialogueAI activity in your course
2. Read any instructions provided by your instructor
3. Start chatting with the AI assistant
4. Complete the required number of interactions
5. Receive completion confirmation when finished
6. Use the "Restart Conversation" button if needed (with confirmation)

### For Instructors
1. Create activities with specific learning objectives
2. Choose appropriate OpenAI model based on complexity and budget
3. Upload relevant documentation (text files) to provide context
4. Set completion requirements (number of questions)
5. Monitor student progress through Moodle's completion tracking
6. Review conversation logs for assessment purposes

## Privacy & Security

- All conversations are stored securely in your Moodle database
- API communications are encrypted via HTTPS
- Student data is handled according to your institution's privacy policies
- Content sanitization prevents XSS attacks
- Rate limiting prevents abuse
- No conversation data is stored by OpenAI when using their API

## Technical Details

### Database Tables
- `mdl_dialogueai`: Stores activity configurations including model selection
- `mdl_dialogueai_conversations`: Stores conversation messages with timestamps

### Key Features Implementation
- **Model Selection**: Dynamic API calls based on selected OpenAI model
- **Character Limits**: Model-aware validation and truncation
- **Completion Tracking**: Integration with Moodle's completion system
- **Line Break Handling**: Safe DOM manipulation for proper formatting
- **Documentation Processing**: Text file parsing with character limit enforcement

### File Structure
```
dialogueai/
├── ajax.php              # AJAX handlers for chat functionality
├── db/                   # Database definitions and upgrades
│   ├── install.xml       # Initial database schema
│   └── upgrade.php       # Database upgrade scripts
├── lang/en/              # English language strings
│   └── dialogueai.php    # All plugin text strings
├── lib.php              # Core plugin functions
├── mod_form.php         # Activity settings form with validation
├── pix/                 # Plugin icons
├── settings.php         # Admin settings (minimal)
├── version.php          # Plugin version information
└── view.php             # Main activity view with chat interface
```

## 🔄 Version History

### v2024100901 (Latest)
- ✅ Added OpenAI model selection (GPT-3.5, GPT-4 Turbo, GPT-4o)
- ✅ Dynamic character limits based on selected model
- ✅ Real-time UI updates for character limit information
- ✅ Model-aware form validation

### v2024100801
- ✅ Fixed activity completion timing and behavior
- ✅ Removed "view activity" completion option
- ✅ Ensured proper completion marking

### v2024100705
- ✅ Fixed JavaScript syntax errors in chat interface
- ✅ Improved line break handling using DOM methods

### v2024100702
- ✅ Upgraded to GPT-4o model
- ✅ Added 50,000 character limit for documentation
- ✅ Implemented character validation system

### v2024100701
- ✅ Fixed completion message timing
- ✅ Fixed line break display in bot messages
- ✅ Enhanced CSS for proper message formatting

## 🤝 Contributing

We welcome contributions! The plugin is actively maintained and improvements are regularly added based on user feedback.

## 📄 License

This plugin is licensed under the GNU GPL v3. See [LICENSE](LICENSE) for details.

## 🆘 Support

For support, please:
1. Check this documentation
2. Review the technical implementation details
3. Test with different OpenAI models
4. Verify API key configuration

## 🔮 Future Enhancements

### Potential Features
- **Conversation Analytics**: Detailed conversation history and analytics
- **Advanced Prompt Templates**: Pre-built educational prompt templates
- **Integration with Moodle Gradebook**: Automatic grading based on conversation quality
- **Multi-language Support**: Support for additional languages
- **PDF/DOC Processing**: Enhanced document processing beyond text files
- **Conversation Export**: Export conversations for external analysis
- **Custom Model Parameters**: Fine-tuning of temperature, max tokens, etc.
- **Conversation Branching**: Multiple conversation paths based on responses
