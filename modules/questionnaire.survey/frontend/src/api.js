export async function submitSurvey(surveyId, answers) {
    const response = await fetch(
        `/api/surveys/${surveyId}/submit`,
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                answers,
            }),
        }
    )

    return response.json()
}

export async function getSurvey(surveyId) {
    const response = await fetch(`/api/surveys/${surveyId}`)

    const result = await response.json()

    if (typeof result.data === 'string') {
        result.data = JSON.parse(result.data)
    }

    return result
}