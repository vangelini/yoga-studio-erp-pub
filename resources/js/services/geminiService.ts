const API_URL = '/api';

export async function generateCourseDescription(title: string): Promise<string> {
    try {
        const response = await fetch(`${API_URL}/gemini/generate-course-description`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title }),
        });
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'API request failed');
        }
        const data = await response.json();
        return data.description;
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Error generating course description:', error);
        return 'Failed to generate description. Please try again.';
    }
}

export async function getPoseOfTheDay(): Promise<string> {
    try {
        const response = await fetch(`${API_URL}/gemini/pose-of-the-day`);
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'API request failed');
        }
        const poseObject = await response.json();
        return JSON.stringify(poseObject);
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Error fetching pose of the day:', error);
        return JSON.stringify({
            name: 'Error',
            instructions: 'Could not fetch a pose from the universe.',
            benefits: 'Please try refreshing the page.',
        });
    }
}
