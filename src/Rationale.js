const { Component } = wp.element;

class Rationale extends Component {
    state = {
        error: undefined,
        ratSubmitted: false
    };
    /**
     * handleRationaleObj: cleans the user's rationale and updates state
     * Parameter: e, the user's input
     * Fires: when the user presses 'Enter Rationale'
     */
    handleRationaleObj = (e) => {
        // e is event object
        e.preventDefault();

       const rationale = e.target.elements.rationale.value.trim();
       const error = this.props.handleRationale(rationale);

        this.setState(() => ({
            error: error,
            ratSubmitted: true
        }));
    };

    render() {
        return (
            <div id="rationale">
                {!this.state.ratSubmitted &&
                    <div>
                        <p>You chose: <b>{this.props.choice}</b></p>
                        {this.state.error && <p>{this.state.error}</p>}
                        <form onSubmit={this.handleRationaleObj}>
                            <span>Please explain why this response is <b>{this.props.choice}</b>:</span>
                            <textarea name="rationale" cols={40} rows={5} maxLength={1000} required placeholder={"125 words or less"} />
                            <button className="button">Enter Rationale</button>
                        </form>
                    </div>
                }
                {this.state.ratSubmitted &&
                    <div>
                        <p>Thank you!</p>
                        <button onClick={this.props.handleNext}>Next</button>
                    </div>
                }
            </div>
        );
    }
}

export default Rationale;