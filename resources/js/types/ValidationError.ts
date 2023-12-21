type ValidationError = {
    type: string,
    message: { [key : string] : string }
}

export default ValidationError;