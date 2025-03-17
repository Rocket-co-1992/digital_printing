import express from 'express';
import bodyParser from 'body-parser';
import mongoose from 'mongoose';
import routes from './routes';

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Connect to MongoDB
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/grafica-digital', {
    useNewUrlParser: true,
    useUnifiedTopology: true,
});

// Routes
app.use('/api', routes);

// Start the server
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});